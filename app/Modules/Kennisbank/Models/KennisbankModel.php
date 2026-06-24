<?php

namespace App\Modules\Kennisbank\Models;

use App\Core\Database;
use App\Core\Model;

class KennisbankModel extends Model
{
    protected static string $table = 'kennisbank_artikelen';
    protected static array $fillable = ['titel', 'categorie', 'inhoud', 'auteur_id'];

    private const SELECT = "
        SELECT k.*, u.naam AS auteur_naam
        FROM kennisbank_artikelen k
        LEFT JOIN users u ON u.id = k.auteur_id
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY k.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' WHERE k.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
