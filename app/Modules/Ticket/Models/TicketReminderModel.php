<?php

namespace App\Modules\Ticket\Models;

use App\Core\Database;
use App\Core\Model;

/** Logt welke herinnering/escalatie-mails al verstuurd zijn per ticket, zie ticket_herinneringen.xml. */
class TicketReminderModel extends Model
{
    protected static string $table = 'ticket_herinneringen';
    protected static array $fillable = ['ticket_id', 'type'];

    public const TYPE_TWEE_WEKEN = 'twee_weken';
    public const TYPE_DEADLINE = 'deadline';

    /** Is er in de afgelopen $binnenDagen al een herinnering van dit type voor dit ticket verstuurd? */
    public static function alGewaarschuwd(int $ticketId, string $type, int $binnenDagen): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM ticket_herinneringen
             WHERE ticket_id = ? AND type = ? AND verstuurd_op >= (NOW() - INTERVAL ? DAY)
             LIMIT 1'
        );
        $stmt->execute([$ticketId, $type, $binnenDagen]);
        return $stmt->fetchColumn() !== false;
    }

    public static function registreer(int $ticketId, string $type): void
    {
        self::create(['ticket_id' => $ticketId, 'type' => $type]);
    }
}
