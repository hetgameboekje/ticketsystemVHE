<?php

namespace App\Modules\Beheer\Models;

use App\Core\Database;
use App\Core\Model;

class LocatieModel extends Model
{
    protected static string $table = 'locaties';
    protected static array $fillable = ['naam', 'adres', 'latitude', 'longitude', 'zichtbaarheid', 'aanmaker_id'];
    protected static bool $softDeletes = true;

    public const ZICHTBAARHEID_OPTIES = [
        'iedereen' => 'Iedereen',
        'alleen_aanmaker' => 'Alleen de aanmaker',
        'selectie' => 'Selectie van gebruikers',
    ];

    /** Locaties die zichtbaar zijn voor $userId volgens het zichtbaarheidsmodel — gebruikt bij het invullen van Urenstaat. */
    public static function visibleForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT DISTINCT l.*
            FROM locaties l
            LEFT JOIN locatie_gebruikers lg ON lg.locatie_id = l.id AND lg.user_id = ?
            WHERE l.deleted_at IS NULL
              AND (
                l.zichtbaarheid = 'iedereen'
                OR l.aanmaker_id = ?
                OR (l.zichtbaarheid = 'selectie' AND lg.id IS NOT NULL)
              )
            ORDER BY l.naam ASC
        ");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    /** @return int[] user-ids die nu aan deze locatie gekoppeld zijn (voor zichtbaarheid = 'selectie'). */
    public static function gebruikersVoorSelectie(int $locatieId): array
    {
        $stmt = Database::pdo()->prepare('SELECT user_id FROM locatie_gebruikers WHERE locatie_id = ?');
        $stmt->execute([$locatieId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @param int[] $userIds */
    public static function setGebruikersVoorSelectie(int $locatieId, array $userIds): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM locatie_gebruikers WHERE locatie_id = ?')->execute([$locatieId]);

        $stmt = $pdo->prepare('INSERT INTO locatie_gebruikers (locatie_id, user_id) VALUES (?, ?)');
        foreach (array_unique($userIds) as $userId) {
            $stmt->execute([$locatieId, $userId]);
        }
    }
}
