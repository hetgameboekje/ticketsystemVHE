<?php

namespace App\Modules\Device\Models;

use App\Core\Database;
use App\Core\Model;

class SoftwareInventarisModel extends Model
{
    protected static string $table = 'software_inventaris';
    protected static array $fillable = [
        'publisher', 'naam', 'versie', 'platform', 'system_component',
        'eerst_gezien', 'laatst_gezien', 'aantal_apparaten', 'apparaat_ids',
    ];

    /**
     * Levert de volledige inventaris voor de index-tabel (Read-only CRUD-lijst): sorteren/zoeken/
     * pagineren gebeurt daarna via App\Core\TableQuery, net als bij elke andere module-lijst in de
     * app — deze pagina heeft dus geen eigen GET-parameters meer nodig.
     */
    public static function all(string $orderBy = 'naam ASC, versie ASC'): array
    {
        return Database::pdo()->query("SELECT * FROM software_inventaris ORDER BY {$orderBy}")->fetchAll();
    }

    public static function totaalAantal(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM software_inventaris')->fetchColumn();
    }

    public static function laatstGeimporteerdOp(): ?string
    {
        $waarde = Database::pdo()->query('SELECT MAX(created_at) FROM software_inventaris')->fetchColumn();
        return $waarde ?: null;
    }

    /**
     * Leegt de volledige inventaris zonder een nieuwe import — bewust een losse actie i.p.v. enkel
     * via replaceAll(), zodat de tabel ook leeggemaakt kan worden als er (nog) geen nieuwe CSV is.
     */
    public static function leegmaken(): int
    {
        $aantal = self::totaalAantal();
        Database::pdo()->exec('DELETE FROM software_inventaris');
        return $aantal;
    }

    /**
     * Vervangt de volledige inventaris in één transactie — de export bevat steeds de complete
     * software-vloot, dus software die nergens meer geïnstalleerd staat verdwijnt zo vanzelf mee
     * i.p.v. als verouderde rij te blijven hangen (zelfde aanpak als Schijfgebruik::replaceAll()).
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function replaceAll(array $rows): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM software_inventaris');

            foreach ($rows as $row) {
                static::create($row);
            }

            $pdo->commit();

            return count($rows);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
