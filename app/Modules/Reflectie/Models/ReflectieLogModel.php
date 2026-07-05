<?php

namespace App\Modules\Reflectie\Models;

use App\Core\Database;
use App\Core\Model;

class ReflectieLogModel extends Model
{
    protected static string $table = 'reflectie_logs';
    protected static array $fillable = ['reflectie_id', 'user_id', 'titel', 'omschrijving'];

    public static function forReflectie(int $reflectieId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM reflectie_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.reflectie_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$reflectieId]);
        return $stmt->fetchAll();
    }
}
