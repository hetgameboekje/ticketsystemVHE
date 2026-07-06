<?php

namespace App\Modules\Ticket\Models;

use App\Core\Database;
use App\Core\Model;

class TicketModel extends Model
{
    protected static string $table = 'tickets';
    protected static array $fillable = [
        'titel', 'omschrijving', 'opdrachtgever_naam', 'afdeling_id', 'prioriteit',
        'impact', 'schatting_minuten', 'deadline', 'behandelaar_id', 'status', 'aangemaakt_door_id',
        'escalatie_nummer', 'escalatie_instantie',
    ];
    protected static bool $softDeletes = true;

    // 'opgelost'/'gesloten' zijn oude statuswaarden van vóór het samenvoegen tot 'afgehandeld' (zie
    // TicketExcel::STATUS_ALIASSEN). Bestaande rijen met die waarde worden hier genormaliseerd naar
    // 'afgehandeld' zodat filtering/weergave overal consistent blijft, ook zonder dataconversie.
    private const SELECT = "
        SELECT t.*, a.naam AS afdeling_naam, b.naam AS behandelaar_naam,
            CASE WHEN t.status IN ('opgelost', 'gesloten') THEN 'afgehandeld' ELSE t.status END AS status
        FROM tickets t
        LEFT JOIN afdelingen a ON a.id = t.afdeling_id
        LEFT JOIN users b ON b.id = t.behandelaar_id
        WHERE t.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        $sql = self::SELECT . ' ORDER BY t.created_at DESC';
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function recent(int $limit = 5): array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' ORDER BY t.created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actieve tickets voor het dashboard: eerst alles 'in_behandeling', dan alles 'open',
     * dan alles 'wacht_op_info' — elk blok intern gesorteerd op meest recent. Afgehandelde
     * (incl. de oude 'opgelost'/'gesloten' waarden) tickets komen hier nooit in voor.
     */
    public static function actief(int $limit = 5): array
    {
        $join = 'LEFT JOIN afdelingen a ON a.id = t.afdeling_id LEFT JOIN users b ON b.id = t.behandelaar_id';
        $branch = fn (string $status, int $volgorde) => "
            (SELECT t.*, a.naam AS afdeling_naam, b.naam AS behandelaar_naam, {$volgorde} AS volgorde
             FROM tickets t {$join}
             WHERE t.deleted_at IS NULL AND t.status = '{$status}'
             ORDER BY t.created_at DESC)
        ";

        $sql = 'SELECT * FROM ('
            . $branch('in_behandeling', 0) . 'UNION ALL' . $branch('open', 1) . 'UNION ALL' . $branch('wacht_op_info', 2)
            . ') gecombineerd ORDER BY volgorde ASC, created_at DESC LIMIT ?';

        $stmt = Database::pdo()->prepare($sql);
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND t.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countByStatus(string $status): int
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM tickets WHERE status = ? AND deleted_at IS NULL');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public static function setCreatedAt(int $id, string $date): void
    {
        $stmt = Database::pdo()->prepare('UPDATE tickets SET created_at = ? WHERE id = ?');
        $stmt->execute([$date . ' 00:00:00', $id]);
    }

    public static function existsByTitelEnOpdrachtgever(string $titel, string $opdrachtgever): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM tickets WHERE LOWER(titel) = LOWER(?) AND LOWER(opdrachtgever_naam) = LOWER(?) AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([trim($titel), trim($opdrachtgever)]);
        return $stmt->fetchColumn() !== false;
    }

    /** Dedupe-check voor de e-mail-intake (zie TicketEmailIntakeController): zelfde afzender+titel in de afgelopen $dagen. */
    public static function existsRecentByAfzenderEnTitel(string $titel, string $afzender, int $dagen = 30): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM tickets
             WHERE LOWER(titel) = LOWER(?) AND LOWER(opdrachtgever_naam) = LOWER(?)
               AND created_at >= (NOW() - INTERVAL ? DAY) AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([trim($titel), trim($afzender), $dagen]);
        return $stmt->fetchColumn() !== false;
    }

    public static function findDuplicateGroups(): array
    {
        $sql = "
            SELECT LOWER(TRIM(titel)) AS titel_key, LOWER(TRIM(opdrachtgever_naam)) AS opdrachtgever_key, COUNT(*) AS aantal
            FROM tickets
            WHERE deleted_at IS NULL
            GROUP BY titel_key, opdrachtgever_key
            HAVING COUNT(*) > 1
        ";
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function findByTitelEnOpdrachtgeverKey(string $titelKey, string $opdrachtgeverKey): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT t.*, (SELECT COUNT(*) FROM ticket_logs l WHERE l.ticket_id = t.id) AS log_count
            FROM tickets t
            WHERE LOWER(TRIM(t.titel)) = ? AND LOWER(TRIM(t.opdrachtgever_naam)) = ? AND t.deleted_at IS NULL
            ORDER BY t.id ASC
        ");
        $stmt->execute([$titelKey, $opdrachtgeverKey]);
        return $stmt->fetchAll();
    }
}
