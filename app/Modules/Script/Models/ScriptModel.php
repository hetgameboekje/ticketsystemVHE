<?php

namespace App\Modules\Script\Models;

use App\Core\Database;
use App\Core\Model;

class ScriptModel extends Model
{
    protected static string $table = 'scripts';
    protected static array $fillable = ['titel', 'omschrijving', 'type', 'inhoud', 'auteur_id'];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT s.*, u.naam AS auteur_naam
        FROM scripts s
        LEFT JOIN users u ON u.id = s.auteur_id
        WHERE s.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY s.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND s.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
