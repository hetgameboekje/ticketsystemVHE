<?php

namespace App\Shared\Rechten\Models;

use App\Core\Database;
use App\Core\Model;

class RechtenModel extends Model
{
    protected static string $table = 'rechten';
    protected static array $fillable = ['user_id', 'module', 'mag_lezen', 'mag_schrijven', 'mag_verwijderen'];

    /** Alle modules die via de rechtenmatrix worden beheerd (module-key zoals gebruikt in $activeModule). */
    public const MODULES = [
        'tickets' => 'Ticket systeem',
        'verbeterpunten' => 'Verbeterpunten',
        'reflecties' => 'Reflectie',
        'kennisbank' => 'Kennisbank',
        'hardware' => 'Hardware-uitgaven',
        'medewerkers' => 'Medewerkers',
        'voorraad' => 'Voorraad',
        'uitgiften' => 'Uitgifte',
        'printers' => 'Printers',
        'cyberrisicos' => "Cyberrisico's",
    ];

    public static function has(int $userId, string $module, string $actie): bool
    {
        $column = match ($actie) {
            'lezen' => 'mag_lezen',
            'schrijven' => 'mag_schrijven',
            'verwijderen' => 'mag_verwijderen',
            default => null,
        };
        if ($column === null) {
            return false;
        }

        $stmt = Database::pdo()->prepare("SELECT {$column} FROM rechten WHERE user_id = ? AND module = ?");
        $stmt->execute([$userId, $module]);
        $value = $stmt->fetchColumn();

        return $value !== false && (int) $value === 1;
    }

    /** @return array<string, array{mag_lezen:int,mag_schrijven:int,mag_verwijderen:int}> */
    public static function forUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM rechten WHERE user_id = ?');
        $stmt->execute([$userId]);

        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['module']] = $row;
        }

        return $out;
    }

    /** @param array<string, array{lezen?:mixed,schrijven?:mixed,verwijderen?:mixed}> $moduleRechten */
    public static function setForUser(int $userId, array $moduleRechten): void
    {
        $pdo = Database::pdo();

        foreach (self::MODULES as $module => $label) {
            $r = $moduleRechten[$module] ?? [];
            $data = [
                'mag_lezen' => !empty($r['lezen']) ? 1 : 0,
                'mag_schrijven' => !empty($r['schrijven']) ? 1 : 0,
                'mag_verwijderen' => !empty($r['verwijderen']) ? 1 : 0,
            ];

            $stmt = $pdo->prepare('SELECT id FROM rechten WHERE user_id = ? AND module = ?');
            $stmt->execute([$userId, $module]);
            $id = $stmt->fetchColumn();

            if ($id !== false) {
                self::update((int) $id, $data);
            } else {
                $data['user_id'] = $userId;
                $data['module'] = $module;
                self::create($data);
            }
        }
    }
}
