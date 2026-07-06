<?php

namespace App\Modules\Kennisbank\Models;

use App\Core\Database;
use App\Core\Model;

class KennisbankModel extends Model
{
    protected static string $table = 'kennisbank_artikelen';
    protected static array $fillable = [
        'titel', 'categorie', 'inhoud', 'auteur_id',
        'quick_action_type', 'quick_action_omschrijving', 'quick_action_script',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT k.*, u.naam AS auteur_naam
        FROM kennisbank_artikelen k
        LEFT JOIN users u ON u.id = k.auteur_id
        WHERE k.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY k.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND k.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Bestaande categorieën, voor het voorstellen van dezelfde naam bij tickets (zie TicketController). */
    public static function distinctCategorieen(): array
    {
        $stmt = Database::pdo()->query(
            "SELECT DISTINCT categorie FROM kennisbank_artikelen WHERE deleted_at IS NULL ORDER BY categorie ASC"
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
