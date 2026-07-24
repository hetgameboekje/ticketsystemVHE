<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;
use App\Core\Model;

/** Benoemde profielen (bv. "Engineer", "Test kabels") met extra items bovenop de hoofdlijst. */
class InstallatieProfielModel extends Model
{
    protected static string $table = 'installatie_profielen';
    protected static array $fillable = ['naam'];

    public static function alle(): array
    {
        return Database::pdo()->query('SELECT * FROM installatie_profielen ORDER BY naam ASC')->fetchAll();
    }

    /** Profielen inclusief hun items, gegroepeerd — voor het beheerscherm. */
    public static function alleMetItems(): array
    {
        $profielen = self::alle();
        $items = InstallatieProfielItemModel::alle();

        foreach ($profielen as &$profiel) {
            $profiel['items'] = array_values(array_filter(
                $items,
                fn (array $item) => (int) $item['profiel_id'] === (int) $profiel['id']
            ));
        }

        return $profielen;
    }
}
