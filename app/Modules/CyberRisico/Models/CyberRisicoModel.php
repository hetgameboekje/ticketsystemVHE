<?php

namespace App\Modules\CyberRisico\Models;

use App\Core\Database;
use App\Core\Model;

class CyberRisicoModel extends Model
{
    protected static string $table = 'cyberrisicos';
    protected static array $fillable = [
        'titel', 'omschrijving', 'categorie', 'prioriteit', 'status', 'locatie', 'gemeld_door',
        'eigenaar_id', 'datum_geconstateerd', 'datum_gemeld', 'oplossingsadvies', 'bewijs_notities',
        'is_gevoelig', 'aangemaakt_door_id',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT c.*, u.naam AS eigenaar_naam
        FROM cyberrisicos c
        LEFT JOIN users u ON u.id = c.eigenaar_id
        WHERE c.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY c.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND c.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countOpen(): int
    {
        return (int) Database::pdo()->query(
            "SELECT COUNT(*) FROM cyberrisicos WHERE deleted_at IS NULL AND status NOT IN ('opgelost', 'geaccepteerd')"
        )->fetchColumn();
    }

    /**
     * Aantal gemelde incidenten per dag over de afgelopen 30 dagen (incl. vandaag),
     * met dagen zonder meldingen op 0 gevuld zodat de grafiek geen gaten heeft.
     * @return array<int, array{datum: string, aantal: int}>
     */
    public static function countLast30Days(): array
    {
        $sql = "
            SELECT COALESCE(datum_gemeld, DATE(created_at)) AS dag, COUNT(*) AS aantal
            FROM cyberrisicos
            WHERE deleted_at IS NULL
              AND COALESCE(datum_gemeld, DATE(created_at)) >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY dag
        ";
        $rows = Database::pdo()->query($sql)->fetchAll();
        $byDate = array_column($rows, 'aantal', 'dag');

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $datum = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = ['datum' => $datum, 'aantal' => (int) ($byDate[$datum] ?? 0)];
        }

        return $result;
    }

    /**
     * Gemelde incidenten van de afgelopen 30 dagen, gegroepeerd per dag — gebruikt om
     * bij een klik op een balk in de dashboard-grafiek de incidenten van die dag te tonen.
     * @return array<string, array<int, array{id: int, titel: string, prioriteit: string, status: string}>>
     */
    public static function listLast30DaysGrouped(): array
    {
        $sql = "
            SELECT id, titel, prioriteit, status, COALESCE(datum_gemeld, DATE(created_at)) AS dag
            FROM cyberrisicos
            WHERE deleted_at IS NULL
              AND COALESCE(datum_gemeld, DATE(created_at)) >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            ORDER BY dag ASC, id ASC
        ";
        $rows = Database::pdo()->query($sql)->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['dag']][] = [
                'id' => (int) $row['id'],
                'titel' => $row['titel'],
                'prioriteit' => $row['prioriteit'],
                'status' => $row['status'],
            ];
        }

        return $grouped;
    }
}
