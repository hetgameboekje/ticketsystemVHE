<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;
use App\Core\Model;

/** De hoofdlijst met applicaties voor de installatie-checklist (zie Tools > Installatie). */
class InstallatieApplicatieModel extends Model
{
    protected static string $table = 'installatie_applicaties';
    protected static array $fillable = ['naam', 'volgorde'];

    public static function alle(): array
    {
        return Database::pdo()->query('SELECT * FROM installatie_applicaties ORDER BY volgorde ASC, id ASC')->fetchAll();
    }

    public static function toevoegen(string $naam): int
    {
        $volgende = (int) Database::pdo()
            ->query('SELECT COALESCE(MAX(volgorde), 0) + 1 FROM installatie_applicaties')
            ->fetchColumn();

        return self::create(['naam' => $naam, 'volgorde' => $volgende]);
    }
}
