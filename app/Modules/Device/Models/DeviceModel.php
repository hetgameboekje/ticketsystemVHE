<?php

namespace App\Modules\Device\Models;

use App\Core\Database;
use App\Core\Model;

class DeviceModel extends Model
{
    protected static string $table = 'devices';
    protected static array $fillable = ['naam', 'extern_apparaat_id', 'medewerker_id', 'laatst_geimporteerd_op'];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT d.*, CONCAT(m.voornaam, ' ', m.achternaam) AS medewerker_naam,
               (SELECT COUNT(*) FROM device_software ds WHERE ds.device_id = d.id) AS software_aantal
        FROM devices d
        LEFT JOIN medewerkers m ON m.id = d.medewerker_id AND m.deleted_at IS NULL
        WHERE d.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY d.naam ASC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND d.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Herkent een apparaat bij een volgende CSV-upload via het ID uit de "Devices"-kolom van de
     * export. Zoekt bewust ook onder verwijderde apparaten — extern_apparaat_id is uniek over de
     * hele tabel (ook soft-deleted rijen), dus zonder dit zou een hernieuwde import van een eerder
     * verwijderd apparaat crashen op een dubbele-sleutel-fout in plaats van het te heractiveren.
     */
    public static function findByExternId(string $externId): ?array
    {
        $stmt = Database::pdo()->prepare("
            SELECT d.*, CONCAT(m.voornaam, ' ', m.achternaam) AS medewerker_naam,
                   (SELECT COUNT(*) FROM device_software ds WHERE ds.device_id = d.id) AS software_aantal
            FROM devices d
            LEFT JOIN medewerkers m ON m.id = d.medewerker_id AND m.deleted_at IS NULL
            WHERE d.extern_apparaat_id = ?
        ");
        $stmt->execute([$externId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function forMedewerker(int $medewerkerId): array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND d.medewerker_id = ? ORDER BY d.naam ASC');
        $stmt->execute([$medewerkerId]);
        return $stmt->fetchAll();
    }
}
