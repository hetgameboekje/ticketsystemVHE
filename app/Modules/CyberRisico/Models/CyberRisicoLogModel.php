<?php

namespace App\Modules\CyberRisico\Models;

use App\Core\Database;
use App\Core\Model;

class CyberRisicoLogModel extends Model
{
    protected static string $table = 'cyberrisico_logs';
    protected static array $fillable = ['cyberrisico_id', 'user_id', 'titel', 'omschrijving'];

    public static function forCyberRisico(int $cyberrisicoId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM cyberrisico_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.cyberrisico_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$cyberrisicoId]);
        return $stmt->fetchAll();
    }
}
