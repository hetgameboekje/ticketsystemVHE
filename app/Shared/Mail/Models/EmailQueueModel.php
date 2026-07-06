<?php

namespace App\Shared\Mail\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Wachtrij voor uitgaande e-mail (zie email_queue.xml). Regels (opgegeven door gebruiker):
 * - Een nieuwe e-mail mag alleen toegevoegd worden als de wachtrij leeg is — dit voorkomt dat de
 *   wachtrij ongecontroleerd groeit wanneer het verzendproces (EmailQueueProcessor) vastloopt.
 * - "Leeg" betekent: geen rijen met een status anders dan 'sent'. Status 'test' is hierop een
 *   uitzondering — testmails tellen niet mee als "bezet".
 */
class EmailQueueModel extends Model
{
    protected static string $table = 'email_queue';
    protected static array $fillable = ['ontvanger', 'onderwerp', 'inhoud', 'status'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_ERROR = 'error';
    public const STATUS_TEST = 'test';

    public static function isLeeg(): bool
    {
        $stmt = Database::pdo()->query(
            "SELECT COUNT(*) FROM email_queue WHERE status NOT IN ('" . self::STATUS_SENT . "', '" . self::STATUS_TEST . "')"
        );
        return (int) $stmt->fetchColumn() === 0;
    }

    /** @return int|null Het nieuwe id, of null als de e-mail niet is toegevoegd omdat de wachtrij niet leeg was. */
    public static function voegToe(string $ontvanger, string $onderwerp, string $inhoud, bool $isTest = false): ?int
    {
        if (!$isTest && !self::isLeeg()) {
            return null;
        }

        return self::create([
            'ontvanger' => $ontvanger,
            'onderwerp' => $onderwerp,
            'inhoud' => $inhoud,
            'status' => $isTest ? self::STATUS_TEST : self::STATUS_PENDING,
        ]);
    }

    /** Openstaande e-mails (nog niet verzonden), oudste eerst — dit is wat EmailQueueProcessor oppakt. */
    public static function openstaand(): array
    {
        $stmt = Database::pdo()->query(
            "SELECT * FROM email_queue WHERE status = '" . self::STATUS_PENDING . "' ORDER BY created_at ASC"
        );
        return $stmt->fetchAll();
    }

    public static function markVerzonden(int $id): void
    {
        Database::pdo()
            ->prepare("UPDATE email_queue SET status = ?, sent_at = NOW() WHERE id = ?")
            ->execute([self::STATUS_SENT, $id]);
    }

    public static function markFout(int $id, string $melding): void
    {
        Database::pdo()
            ->prepare('UPDATE email_queue SET status = ?, foutmelding = ? WHERE id = ?')
            ->execute([self::STATUS_ERROR, $melding, $id]);
    }

    /** Voor het Beheer-overzicht: alle e-mails, nieuwste eerst. */
    public static function alle(): array
    {
        return Database::pdo()->query('SELECT * FROM email_queue ORDER BY created_at DESC')->fetchAll();
    }
}
