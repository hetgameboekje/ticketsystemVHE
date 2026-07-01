<?php

namespace App\Modules\Reflectie\Models;

use App\Core\Database;
use App\Core\Model;

class ReflectieModel extends Model
{
    protected static string $table = 'reflecties';
    protected static array $fillable = ['titel', 'periode', 'inhoud', 'gebruiker_id'];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT r.*, u.naam AS gebruiker_naam
        FROM reflecties r
        LEFT JOIN users u ON u.id = r.gebruiker_id
        WHERE r.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY r.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND r.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
