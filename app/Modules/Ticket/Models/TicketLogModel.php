<?php

namespace App\Modules\Ticket\Models;

use App\Core\Database;
use App\Core\Model;

class TicketLogModel extends Model
{
    protected static string $table = 'ticket_logs';
    protected static array $fillable = ['ticket_id', 'user_id', 'opmerking', 'status_van', 'status_naar'];

    public static function forTicket(int $ticketId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM ticket_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.ticket_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }
}
