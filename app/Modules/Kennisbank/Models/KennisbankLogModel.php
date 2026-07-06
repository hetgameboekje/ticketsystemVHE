<?php

namespace App\Modules\Kennisbank\Models;

use App\Core\Database;
use App\Core\Model;

class KennisbankLogModel extends Model
{
    protected static string $table = 'kennisbank_logs';
    protected static array $fillable = ['kennisbank_artikel_id', 'user_id', 'titel', 'omschrijving', 'volgorde'];

    /** Gesorteerd als artikelen: laagste volgorde bovenaan, bij gelijke volgorde de nieuwste eerst. */
    public static function forArtikel(int $artikelId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT l.*, u.naam AS user_naam
            FROM kennisbank_logs l
            LEFT JOIN users u ON u.id = l.user_id
            WHERE l.kennisbank_artikel_id = ?
            ORDER BY l.volgorde ASC, l.created_at DESC
        ");
        $stmt->execute([$artikelId]);
        return $stmt->fetchAll();
    }

    public static function updateVolgorde(int $id, int $volgorde): void
    {
        $stmt = Database::pdo()->prepare('UPDATE kennisbank_logs SET volgorde = ? WHERE id = ?');
        $stmt->execute([$volgorde, $id]);
    }
}
