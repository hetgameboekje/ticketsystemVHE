<?php

namespace App\Modules\Schijfgebruik\Models;

use App\Core\Database;
use App\Core\Model;

class SchijfgebruikSchijfModel extends Model
{
    protected static string $table = 'schijfgebruik_schijven';
    protected static array $fillable = [
        'device_id', 'letter', 'disk_type', 'capaciteit_bytes', 'capaciteit_label', 'gebruik_percentage',
    ];

    /** @return array<int, array<string, mixed>> één rij per schijf, met de bijbehorende apparaatgegevens erbij gejoined. */
    public static function allWithDevice(): array
    {
        return Database::pdo()->query("
            SELECT
                s.id AS schijf_id, s.letter, s.disk_type, s.capaciteit_bytes, s.capaciteit_label, s.gebruik_percentage,
                d.id AS device_id, d.extern_id, d.organisatie, d.locatie, d.naam, d.type, d.rol, d.beleid,
                d.laatst_online, d.laatst_update, d.laatste_login, d.merk, d.model, d.serienummer, d.os_naam
            FROM schijfgebruik_schijven s
            JOIN schijfgebruik_devices d ON d.id = s.device_id
            ORDER BY s.gebruik_percentage DESC
        ")->fetchAll();
    }
}
