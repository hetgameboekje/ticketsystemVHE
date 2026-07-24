<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;
use App\Core\Model;

/** Een toewijzing van de installatie-checklist aan één apparaat (zie Tools > Installatie). */
class InstallatieOpdrachtModel extends Model
{
    protected static string $table = 'installatie_opdrachten';
    protected static array $fillable = ['device_id', 'opmerking', 'toegewezen_door_id'];

    private const SELECT = "
        SELECT o.*, d.naam AS apparaat_naam,
               (SELECT COUNT(*) FROM installatie_opdracht_items i WHERE i.opdracht_id = o.id) AS items_totaal,
               (SELECT COUNT(*) FROM installatie_opdracht_items i WHERE i.opdracht_id = o.id AND i.afgevinkt = 1) AS items_afgevinkt
        FROM installatie_opdrachten o
        LEFT JOIN devices d ON d.id = o.device_id
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY o.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE o.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Maakt een opdracht aan met een bevroren snapshot van de hoofdlijst + gekozen profielen —
     * latere wijzigingen aan de hoofdlijst/profielen raken een eenmaal aangemaakte opdracht dus niet.
     *
     * @param int[] $profielIds
     */
    public static function aanmaken(int $deviceId, array $profielIds, ?string $opmerking, ?int $toegewezenDoorId): int
    {
        $opdrachtId = self::create([
            'device_id' => $deviceId,
            'opmerking' => $opmerking,
            'toegewezen_door_id' => $toegewezenDoorId,
        ]);

        $volgorde = 0;
        foreach (InstallatieApplicatieModel::alle() as $app) {
            self::voegItemToe($opdrachtId, $app['naam'], $volgorde++);
        }

        foreach (InstallatieProfielModel::alle() as $profiel) {
            if (!in_array((int) $profiel['id'], $profielIds, true)) {
                continue;
            }

            foreach (InstallatieProfielItemModel::forProfiel((int) $profiel['id']) as $item) {
                self::voegItemToe($opdrachtId, $item['naam'], $volgorde++);
            }

            Database::pdo()
                ->prepare('INSERT INTO installatie_opdracht_profielen (opdracht_id, profiel_naam) VALUES (?, ?)')
                ->execute([$opdrachtId, $profiel['naam']]);
        }

        return $opdrachtId;
    }

    private static function voegItemToe(int $opdrachtId, string $naam, int $volgorde): void
    {
        Database::pdo()
            ->prepare('INSERT INTO installatie_opdracht_items (opdracht_id, naam, volgorde) VALUES (?, ?, ?)')
            ->execute([$opdrachtId, $naam, $volgorde]);
    }

    public static function items(int $opdrachtId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM installatie_opdracht_items WHERE opdracht_id = ? ORDER BY volgorde ASC, id ASC');
        $stmt->execute([$opdrachtId]);
        return $stmt->fetchAll();
    }

    public static function profielNamen(int $opdrachtId): array
    {
        $stmt = Database::pdo()->prepare('SELECT profiel_naam FROM installatie_opdracht_profielen WHERE opdracht_id = ?');
        $stmt->execute([$opdrachtId]);
        return array_column($stmt->fetchAll(), 'profiel_naam');
    }

    public static function toggleItem(int $opdrachtId, int $itemId): void
    {
        $stmt = Database::pdo()->prepare('SELECT afgevinkt FROM installatie_opdracht_items WHERE id = ? AND opdracht_id = ?');
        $stmt->execute([$itemId, $opdrachtId]);
        $huidig = $stmt->fetchColumn();
        if ($huidig === false) {
            return;
        }

        $nieuw = ((int) $huidig) === 1 ? 0 : 1;
        Database::pdo()
            ->prepare('UPDATE installatie_opdracht_items SET afgevinkt = ?, afgevinkt_op = ? WHERE id = ?')
            ->execute([$nieuw, $nieuw === 1 ? date('Y-m-d H:i:s') : null, $itemId]);
    }
}
