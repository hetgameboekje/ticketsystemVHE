<?php

namespace App\Modules\Medewerker\Models;

use App\Core\Database;
use App\Core\Model;

class MedewerkerModel extends Model
{
    protected static string $table = 'medewerkers';
    protected static array $fillable = [
        'voornaam', 'achternaam', 'email', 'telefoon', 'functie', 'afdeling_id', 'startdatum', 'status',
    ];

    private const SELECT = "
        SELECT m.*, a.naam AS afdeling_naam
        FROM medewerkers m
        LEFT JOIN afdelingen a ON a.id = m.afdeling_id
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY m.achternaam ASC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE m.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
