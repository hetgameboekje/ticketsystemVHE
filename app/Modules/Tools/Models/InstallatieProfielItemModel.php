<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;
use App\Core\Model;

class InstallatieProfielItemModel extends Model
{
    protected static string $table = 'installatie_profiel_items';
    protected static array $fillable = ['profiel_id', 'naam', 'volgorde'];

    public static function alle(): array
    {
        return Database::pdo()->query('SELECT * FROM installatie_profiel_items ORDER BY volgorde ASC, id ASC')->fetchAll();
    }

    public static function forProfiel(int $profielId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM installatie_profiel_items WHERE profiel_id = ? ORDER BY volgorde ASC, id ASC');
        $stmt->execute([$profielId]);
        return $stmt->fetchAll();
    }

    public static function toevoegen(int $profielId, string $naam): int
    {
        $stmt = Database::pdo()->prepare('SELECT COALESCE(MAX(volgorde), 0) + 1 FROM installatie_profiel_items WHERE profiel_id = ?');
        $stmt->execute([$profielId]);
        $volgende = (int) $stmt->fetchColumn();

        return self::create(['profiel_id' => $profielId, 'naam' => $naam, 'volgorde' => $volgende]);
    }
}
