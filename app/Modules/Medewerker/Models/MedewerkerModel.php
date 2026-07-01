<?php

namespace App\Modules\Medewerker\Models;

use App\Core\Database;
use App\Core\Model;

class MedewerkerModel extends Model
{
    protected static string $table = 'medewerkers';
    protected static array $fillable = [
        'voornaam', 'achternaam', 'email', 'telefoon', 'functie', 'afdeling_id', 'startdatum', 'status', 'user_id',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT m.*, a.naam AS afdeling_naam, u.naam AS login_naam, u.email AS login_email, u.deleted_at AS login_deleted_at
        FROM medewerkers m
        LEFT JOIN afdelingen a ON a.id = m.afdeling_id
        LEFT JOIN users u ON u.id = m.user_id
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

    /** Actieve logins die nog aan geen (andere) medewerker gekoppeld zijn, plus optioneel de huidige koppeling zelf. */
    public static function beschikbareGebruikers(?int $huidigeUserId = null): array
    {
        $sql = 'SELECT u.* FROM users u
                WHERE u.deleted_at IS NULL
                AND (u.id NOT IN (SELECT user_id FROM medewerkers WHERE user_id IS NOT NULL AND deleted_at IS NULL)';
        $params = [];
        if ($huidigeUserId !== null) {
            $sql .= ' OR u.id = ?';
            $params[] = $huidigeUserId;
        }
        $sql .= ') ORDER BY u.naam ASC';

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
