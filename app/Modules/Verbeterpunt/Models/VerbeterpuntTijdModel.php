<?php

namespace App\Modules\Verbeterpunt\Models;

use App\Core\Database;
use App\Core\Model;

class VerbeterpuntTijdModel extends Model
{
    protected static string $table = 'verbeterpunt_tijdregistraties';
    protected static array $fillable = ['verbeterpunt_id', 'user_id', 'minuten'];

    public static function forVerbeterpunt(int $verbeterpuntId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT t.*, u.naam AS user_naam
            FROM verbeterpunt_tijdregistraties t
            LEFT JOIN users u ON u.id = t.user_id
            WHERE t.verbeterpunt_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$verbeterpuntId]);
        return $stmt->fetchAll();
    }

    public static function sumForVerbeterpunt(int $verbeterpuntId): int
    {
        $stmt = Database::pdo()->prepare(
            "SELECT COALESCE(SUM(minuten), 0) FROM verbeterpunt_tijdregistraties WHERE verbeterpunt_id = ?"
        );
        $stmt->execute([$verbeterpuntId]);
        return (int) $stmt->fetchColumn();
    }
}
