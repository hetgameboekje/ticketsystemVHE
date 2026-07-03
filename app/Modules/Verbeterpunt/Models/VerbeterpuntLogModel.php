<?php

namespace App\Modules\Verbeterpunt\Models;

use App\Core\Database;
use App\Core\Model;

class VerbeterpuntLogModel extends Model
{
    protected static string $table = 'verbeterpunt_logs';
    protected static array $fillable = ['verbeterpunt_id', 'user_id', 'opmerking', 'status_van', 'status_naar'];

    public static function forVerbeterpunt(int $verbeterpuntId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM verbeterpunt_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.verbeterpunt_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$verbeterpuntId]);
        return $stmt->fetchAll();
    }
}
