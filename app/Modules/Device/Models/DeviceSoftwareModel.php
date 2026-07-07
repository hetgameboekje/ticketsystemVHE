<?php

namespace App\Modules\Device\Models;

use App\Core\Database;
use App\Core\Model;

class DeviceSoftwareModel extends Model
{
    protected static string $table = 'device_software';
    protected static array $fillable = [
        'device_id', 'publisher', 'naam', 'versie', 'platform', 'system_component', 'eerst_gezien', 'laatst_gezien',
    ];

    public static function forDevice(int $deviceId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM device_software WHERE device_id = ? ORDER BY publisher ASC, naam ASC'
        );
        $stmt->execute([$deviceId]);
        return $stmt->fetchAll();
    }

    /**
     * Vervangt de volledige software-lijst van een apparaat door een nieuwe CSV-snapshot (zie
     * DeviceSoftwareImport) — zo verdwijnt gedeïnstalleerde software vanzelf uit het overzicht.
     */
    public static function replaceForDevice(int $deviceId, array $rows): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $pdo->prepare('DELETE FROM device_software WHERE device_id = ?')->execute([$deviceId]);

        foreach ($rows as $row) {
            self::create($row + ['device_id' => $deviceId]);
        }

        $pdo->commit();
    }
}
