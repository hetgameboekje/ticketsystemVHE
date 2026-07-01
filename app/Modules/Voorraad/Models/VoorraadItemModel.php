<?php

namespace App\Modules\Voorraad\Models;

use App\Core\Database;
use App\Core\Model;

class VoorraadItemModel extends Model
{
    protected static string $table = 'voorraad_items';
    protected static array $fillable = [
        'type_id', 'variant', 'serienummer', 'barcode', 'status', 'locatie', 'opmerking', 'aangemaakt_door_id',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT vi.*, vt.naam AS type_naam, vt.code AS type_code
        FROM voorraad_items vi
        LEFT JOIN voorraad_types vt ON vt.id = vi.type_id
        WHERE vi.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY vi.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND vi.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countByType(): array
    {
        $sql = "
            SELECT vt.id, vt.naam, vt.code,
                   COUNT(vi.id) AS totaal,
                   SUM(CASE WHEN vi.status = 'op_voorraad' THEN 1 ELSE 0 END) AS beschikbaar
            FROM voorraad_types vt
            LEFT JOIN voorraad_items vi ON vi.type_id = vt.id AND vi.deleted_at IS NULL
            GROUP BY vt.id, vt.naam, vt.code
            ORDER BY vt.naam ASC
        ";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function searchAvailable(string $q): array
    {
        $stmt = Database::pdo()->prepare(
            self::SELECT . " AND vi.status = 'op_voorraad' AND (vi.barcode LIKE ? OR vt.naam LIKE ?)
             ORDER BY vt.naam ASC, vi.barcode ASC LIMIT 15"
        );
        $like = '%' . $q . '%';
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public static function findAvailableByBarcode(string $barcode): ?array
    {
        $stmt = Database::pdo()->prepare(
            self::SELECT . " AND vi.barcode = ? AND vi.status = 'op_voorraad' ORDER BY vi.id ASC LIMIT 1"
        );
        $stmt->execute([$barcode]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function serienummerExists(string $serienummer, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM voorraad_items WHERE serienummer = ? AND deleted_at IS NULL';
        $params = [$serienummer];
        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $exceptId;
        }

        $stmt = Database::pdo()->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = Database::pdo()->prepare('UPDATE voorraad_items SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
}
