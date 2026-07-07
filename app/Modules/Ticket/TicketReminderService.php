<?php

namespace App\Modules\Ticket;

use App\Core\Database;
use App\Modules\Ticket\Models\TicketReminderModel;
use App\Shared\Crypto\FieldEncryptor;
use App\Shared\Mail\Models\EmailQueueModel;

/**
 * Genereert herinnerings-/escalatiemails voor tickets:
 * - "Twee weken"-herinnering: een ticket dat nog niet afgehandeld is, elke 14 dagen sinds aanmaken.
 * - "Deadline"-escalatie: een ticket met een deadline die binnen 3 dagen valt (of al voorbij is) en
 *   nog niet afgehandeld is.
 *
 * Bedoeld om periodiek aangeroepen te worden door een externe scheduler via
 * POST /api/tickets/herinneringen. Elke e-mail gaat via EmailQueueModel::voegToe() — als de
 * wachtrij niet leeg is, wordt de herinnering overgeslagen (en dus niet als "verstuurd" gelogd,
 * zodat hij bij de volgende run alsnog opgepakt kan worden).
 */
class TicketReminderService
{
    private const DEADLINE_BINNEN_DAGEN = 3;

    /** @return array{gequeued:int,overgeslagen:int} */
    public static function genereer(): array
    {
        $gequeued = 0;
        $overgeslagen = 0;

        foreach (self::ticketsVoorTweeWekenHerinnering() as $ticket) {
            if (self::queueHerinnering($ticket, TicketReminderModel::TYPE_TWEE_WEKEN, 14, self::tweeWekenOnderwerp($ticket), self::tweeWekenInhoud($ticket))) {
                $gequeued++;
            } else {
                $overgeslagen++;
            }
        }

        foreach (self::ticketsMetNaderendeDeadline() as $ticket) {
            if (self::queueHerinnering($ticket, TicketReminderModel::TYPE_DEADLINE, 1, self::deadlineOnderwerp($ticket), self::deadlineInhoud($ticket))) {
                $gequeued++;
            } else {
                $overgeslagen++;
            }
        }

        return ['gequeued' => $gequeued, 'overgeslagen' => $overgeslagen];
    }

    private static function queueHerinnering(array $ticket, string $type, int $dedupeDagen, string $onderwerp, string $inhoud): bool
    {
        if (empty($ticket['behandelaar_email'])) {
            return false;
        }

        if (TicketReminderModel::alGewaarschuwd((int) $ticket['id'], $type, $dedupeDagen)) {
            return false;
        }

        $id = EmailQueueModel::voegToe($ticket['behandelaar_email'], $onderwerp, $inhoud);
        if ($id === null) {
            return false;
        }

        TicketReminderModel::registreer((int) $ticket['id'], $type);
        return true;
    }

    private static function ticketsVoorTweeWekenHerinnering(): array
    {
        $sql = "
            SELECT t.*, u.email AS behandelaar_email
            FROM tickets t
            LEFT JOIN users u ON u.id = t.behandelaar_id
            WHERE t.deleted_at IS NULL
              AND t.status <> 'afgehandeld'
              AND DATEDIFF(CURDATE(), t.created_at) > 0
              AND DATEDIFF(CURDATE(), t.created_at) % 14 = 0
        ";
        return Database::pdo()->query($sql)->fetchAll();
    }

    private static function ticketsMetNaderendeDeadline(): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT t.*, u.email AS behandelaar_email
            FROM tickets t
            LEFT JOIN users u ON u.id = t.behandelaar_id
            WHERE t.deleted_at IS NULL
              AND t.status <> 'afgehandeld'
              AND t.deadline IS NOT NULL
              AND t.deadline <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->execute([self::DEADLINE_BINNEN_DAGEN]);
        return $stmt->fetchAll();
    }

    private static function tweeWekenOnderwerp(array $t): string
    {
        return "Ticket #{$t['id']} staat al langere tijd open: {$t['titel']}";
    }

    private static function tweeWekenInhoud(array $t): string
    {
        $dagen = (int) (new \DateTime())->diff(new \DateTime($t['created_at']))->format('%a');
        return self::ticketDetailsHtml($t, "Ticket #{$t['id']} staat al {$dagen} dagen open en is nog niet afgehandeld.");
    }

    private static function deadlineOnderwerp(array $t): string
    {
        return "Deadline nadert voor ticket #{$t['id']}: {$t['titel']}";
    }

    private static function deadlineInhoud(array $t): string
    {
        return self::ticketDetailsHtml($t, "Ticket #{$t['id']} heeft een naderende deadline en is nog niet afgehandeld.");
    }

    private const PRIORITEIT_LABELS = ['laag' => 'Laag', 'normaal' => 'Normaal', 'middel' => 'Middel', 'hoog' => 'Hoog', 'kritiek' => 'Kritiek'];

    /**
     * Bouwt de gedeelde e-mailinhoud voor beide herinneringstypes: titel, omschrijving (ontsleuteld —
     * de query hierboven leest direct via Database::pdo(), niet via TicketModel, dus dat gebeurt hier
     * expliciet), aanmaakdatum, deadline, prioriteit en een klikbare link naar het ticket.
     */
    private static function ticketDetailsHtml(array $t, string $intro): string
    {
        $omschrijving = FieldEncryptor::decrypt($t['omschrijving']);
        $prioriteit = self::PRIORITEIT_LABELS[$t['prioriteit']] ?? ucfirst($t['prioriteit']);
        $aangemaaktOp = date('d-m-Y H:i', strtotime($t['created_at']));
        $deadline = $t['deadline'] ? date('d-m-Y', strtotime($t['deadline'])) : '—';
        $link = self::ticketUrl((int) $t['id']);

        return '<p>' . htmlspecialchars($intro) . '</p>'
            . '<p><strong>' . htmlspecialchars($t['titel']) . '</strong><br>'
            . nl2br(htmlspecialchars($omschrijving)) . '</p>'
            . '<ul>'
            . '<li>Aangemaakt op: ' . $aangemaaktOp . '</li>'
            . '<li>Deadline: ' . htmlspecialchars($deadline) . '</li>'
            . '<li>Prioriteit: ' . htmlspecialchars($prioriteit) . '</li>'
            . '</ul>'
            . '<p><a href="' . htmlspecialchars($link) . '">Bekijk ticket #' . $t['id'] . '</a></p>';
    }

    private static function ticketUrl(int $ticketId): string
    {
        $config = require APP_ROOT . '/config/config.php';
        return $config['appUrl'] . '/tickets/' . $ticketId;
    }
}
