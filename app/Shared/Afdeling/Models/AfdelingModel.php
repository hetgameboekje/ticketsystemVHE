<?php

namespace App\Shared\Afdeling\Models;

use App\Core\Database;
use App\Core\Model;

class AfdelingModel extends Model
{
    protected static string $table = 'afdelingen';
    protected static array $fillable = ['naam'];

    public static function all(string $orderBy = 'naam ASC'): array
    {
        return parent::all($orderBy);
    }

    public static function findOrCreateByNaam(string $naam): int
    {
        $naam = trim($naam);

        $stmt = Database::pdo()->prepare('SELECT id FROM afdelingen WHERE LOWER(naam) = LOWER(?)');
        $stmt->execute([$naam]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        return self::create(['naam' => $naam]);
    }
}
