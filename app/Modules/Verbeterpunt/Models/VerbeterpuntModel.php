<?php

namespace App\Modules\Verbeterpunt\Models;

use App\Core\Database;
use App\Core\Model;

class VerbeterpuntModel extends Model
{
    protected static string $table = 'verbeterpunten';
    protected static array $fillable = ['titel', 'omschrijving', 'afdeling_id', 'ingediend_door_id', 'status'];

    private const SELECT = "
        SELECT v.*, a.naam AS afdeling_naam, u.naam AS ingediend_door_naam
        FROM verbeterpunten v
        LEFT JOIN afdelingen a ON a.id = v.afdeling_id
        LEFT JOIN users u ON u.id = v.ingediend_door_id
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY v.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE v.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
