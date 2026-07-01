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
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT m.*, a.naam AS afdeling_naam
        FROM medewerkers m
        LEFT JOIN afdelingen a ON a.id = m.afdeling_id
        WHERE m.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY m.achternaam ASC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND m.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function searchNamen(string $q): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT CONCAT(voornaam, ' ', achternaam) AS naam FROM medewerkers
             WHERE CONCAT(voornaam, ' ', achternaam) LIKE ? AND deleted_at IS NULL ORDER BY achternaam ASC LIMIT 10"
        );
        $stmt->execute(['%' . $q . '%']);
        return array_column($stmt->fetchAll(), 'naam');
    }
}
