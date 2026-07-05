<?php

namespace App\Modules\Kennisbank\Models;

use App\Core\Database;
use App\Core\Model;

class KennisbankLogModel extends Model
{
    protected static string $table = 'kennisbank_logs';
    protected static array $fillable = ['kennisbank_artikel_id', 'user_id', 'titel', 'omschrijving'];

    public static function forArtikel(int $artikelId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM kennisbank_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.kennisbank_artikel_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$artikelId]);
        return $stmt->fetchAll();
    }
}
