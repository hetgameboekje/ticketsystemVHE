<?php

namespace App\Modules\Ticket\Models;

use App\Core\Database;
use App\Core\Model;

class TicketModel extends Model
{
    protected static string $table = 'tickets';
    protected static array $fillable = [
        'titel', 'omschrijving', 'opdrachtgever_naam', 'afdeling_id', 'prioriteit',
        'impact', 'schatting_minuten', 'deadline', 'behandelaar_id', 'status', 'aangemaakt_door_id',
    ];

    private const SELECT = "
        SELECT t.*, a.naam AS afdeling_naam, b.naam AS behandelaar_naam
        FROM tickets t
        LEFT JOIN afdelingen a ON a.id = t.afdeling_id
        LEFT JOIN users b ON b.id = t.behandelaar_id
    ";

    public static function allWithRelations(): array
    {
        $sql = self::SELECT . ' ORDER BY t.created_at DESC';
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function recent(int $limit = 5): array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' ORDER BY t.created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE t.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countByStatus(string $status): int
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM tickets WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }
}
