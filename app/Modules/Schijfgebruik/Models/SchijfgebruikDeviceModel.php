<?php

namespace App\Modules\Schijfgebruik\Models;

use App\Core\Database;
use App\Core\Model;

class SchijfgebruikDeviceModel extends Model
{
    protected static string $table = 'schijfgebruik_devices';
    protected static array $fillable = [
        'extern_id', 'organisatie', 'locatie', 'naam', 'type', 'rol', 'beleid',
        'laatst_online', 'laatst_update', 'laatste_boot', 'garantie_tot', 'tags', 'laatste_login',
        'ip_adressen', 'mac_adressen',
        'publiek_ip', 'geheugen_gib', 'os_naam', 'os_architectuur', 'os_build',
        'merk', 'model', 'serienummer', 'domein', 'processor', 'tijdzone',
    ];

    public static function findWithSchijven(int $id): ?array
    {
        $device = static::find($id);
        if ($device === null) {
            return null;
        }

        $device['schijven'] = SchijfgebruikSchijfModel::forDevice($id);
        return $device;
    }

    /**
     * Vervangt de volledige inventaris in één transactie: wist alle bestaande apparaten/schijven en
     * zet de rijen uit de nieuwe CSV erin. Gekozen boven een update-per-rij omdat de export steeds de
     * volledige vloot bevat — apparaten die er niet meer in staan (afgevoerd/vervangen) verdwijnen dan
     * vanzelf mee, in plaats van als verouderde rij te blijven hangen.
     *
     * @param array<int, array{device: array<string, mixed>, schijven: array<int, array<string, mixed>>}> $parsed
     * @return array{apparaten: int, schijven: int}
     */
    public static function replaceAll(array $parsed): array
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM schijfgebruik_schijven');
            $pdo->exec('DELETE FROM schijfgebruik_devices');

            $apparaten = 0;
            $schijven = 0;

            foreach ($parsed as $entry) {
                $deviceId = static::create($entry['device']);
                $apparaten++;

                foreach ($entry['schijven'] as $schijf) {
                    $schijf['device_id'] = $deviceId;
                    SchijfgebruikSchijfModel::create($schijf);
                    $schijven++;
                }
            }

            $pdo->commit();

            return ['apparaten' => $apparaten, 'schijven' => $schijven];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
