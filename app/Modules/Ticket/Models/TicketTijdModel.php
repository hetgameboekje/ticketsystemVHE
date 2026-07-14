<?php

namespace App\Modules\Ticket\Models;

use App\Core\Database;
use App\Core\Model;

class TicketTijdModel extends Model
{
    protected static string $table = 'ticket_tijdregistraties';
    protected static array $fillable = ['ticket_id', 'user_id', 'minuten'];

    public static function forTicket(int $ticketId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT t.*, u.naam AS user_naam
            FROM ticket_tijdregistraties t
            LEFT JOIN users u ON u.id = t.user_id
            WHERE t.ticket_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public static function sumForTicket(int $ticketId): int
    {
        $stmt = Database::pdo()->prepare(
            "SELECT COALESCE(SUM(minuten), 0) FROM ticket_tijdregistraties WHERE ticket_id = ?"
        );
        $stmt->execute([$ticketId]);
        return (int) $stmt->fetchColumn();
    }
}
