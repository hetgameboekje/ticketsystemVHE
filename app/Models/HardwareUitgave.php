<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class HardwareUitgave extends Model
{
    protected static string $table = 'hardware_uitgaven';
    protected static array $fillable = [
        'omschrijving', 'leverancier', 'bedrag', 'aankoopdatum', 'afdeling_id', 'aangevraagd_door_id', 'status',
    ];

    private const SELECT = "
        SELECT h.*, a.naam AS afdeling_naam, u.naam AS aangevraagd_door_naam
        FROM hardware_uitgaven h
        LEFT JOIN afdelingen a ON a.id = h.afdeling_id
        LEFT JOIN users u ON u.id = h.aangevraagd_door_id
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY h.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE h.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
