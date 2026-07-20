<?php

namespace App\Modules\Urenstaat\Models;

use App\Core\Database;
use App\Core\Model;

class UrenstaatModel extends Model
{
    protected static string $table = 'urenstaat_registraties';
    protected static array $fillable = [
        'user_id', 'locatie_id', 'keyuser_id', 'datum', 'start_tijd', 'eind_tijd', 'omschrijving',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT r.*, u.naam AS gebruiker_naam, l.naam AS locatie_naam,
               CONCAT(k.voornaam, ' ', k.achternaam) AS keyuser_naam
        FROM urenstaat_registraties r
        LEFT JOIN users u ON u.id = r.user_id
        LEFT JOIN locaties l ON l.id = r.locatie_id
        LEFT JOIN medewerkers k ON k.id = r.keyuser_id
        WHERE r.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY r.datum DESC, r.start_tijd DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND r.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function openForUser(int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            self::SELECT . ' AND r.user_id = ? AND r.eind_tijd IS NULL ORDER BY r.id DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function allOpenForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            self::SELECT . ' AND r.user_id = ? AND r.eind_tijd IS NULL ORDER BY r.datum ASC, r.start_tijd ASC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
