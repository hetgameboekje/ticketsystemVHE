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
        'merk', 'model', 'serienummer', 'domein', 'processor', 'tijdzone', 'medewerker_id',
    ];

    /**
     * Gebruikt om vanaf een medewerker-pagina door te klikken naar schijfgebruik: naam is hier
     * altijd de hostnaam uit de NinjaRMM "Display Name"-kolom, dus in tegenstelling tot
     * DeviceModel::findByNaamMatch() is een exacte (case-insensitive) match hier wel betrouwbaar.
     */
    public static function findByNaam(string $naam): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM schijfgebruik_devices WHERE LOWER(naam) = LOWER(?) LIMIT 1');
        $stmt->execute([$naam]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findWithSchijven(int $id): ?array
    {
        $stmt = Database::pdo()->prepare("
            SELECT d.*, CONCAT(m.voornaam, ' ', m.achternaam) AS medewerker_naam
            FROM schijfgebruik_devices d
            LEFT JOIN medewerkers m ON m.id = d.medewerker_id AND m.deleted_at IS NULL
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $device = $stmt->fetch();
        if ($device === false) {
            return null;
        }

        $device['schijven'] = SchijfgebruikSchijfModel::forDevice($id);
        return $device;
    }

    /**
     * Eén rij per apparaat (geen schijf-detail nodig) met de gekoppelde medewerker erbij, als basis
     * voor de herstart-herinneringsmail: welke medewerker moet gevraagd worden zijn/haar apparaat te
     * herstarten. Filtering op SchijfgebruikHealth::evaluate()['herstart_nodig'] gebeurt in de aanroepende code.
     */
    public static function alleMetMedewerker(): array
    {
        return Database::pdo()->query("
            SELECT d.id, d.naam, d.laatst_online, d.laatste_boot, d.medewerker_id,
                   m.voornaam, m.achternaam, m.email
            FROM schijfgebruik_devices d
            LEFT JOIN medewerkers m ON m.id = d.medewerker_id AND m.deleted_at IS NULL
            ORDER BY d.naam ASC
        ")->fetchAll();
    }

    public static function setMedewerker(int $id, ?int $medewerkerId): void
    {
        $stmt = Database::pdo()->prepare('UPDATE schijfgebruik_devices SET medewerker_id = ? WHERE id = ?');
        $stmt->execute([$medewerkerId, $id]);
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
            // De import vervangt de hele tabel, dus handmatig gekoppelde medewerkers gaan anders
            // verloren bij elke nieuwe CSV-upload — hier op naam bewaard en na het herinladen
            // teruggezet.
            $bestaandeKoppelingen = [];
            foreach ($pdo->query('SELECT naam, medewerker_id FROM schijfgebruik_devices WHERE medewerker_id IS NOT NULL') as $rij) {
                $bestaandeKoppelingen[strtolower($rij['naam'])] = $rij['medewerker_id'];
            }

            $pdo->exec('DELETE FROM schijfgebruik_schijven');
            $pdo->exec('DELETE FROM schijfgebruik_devices');

            $apparaten = 0;
            $schijven = 0;

            foreach ($parsed as $entry) {
                $naamKey = strtolower($entry['device']['naam']);
                if (isset($bestaandeKoppelingen[$naamKey])) {
                    $entry['device']['medewerker_id'] = $bestaandeKoppelingen[$naamKey];
                }

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
