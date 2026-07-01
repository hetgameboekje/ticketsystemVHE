<?php

namespace App\Modules\Agenda\Models;

use App\Core\Database;
use App\Core\Model;

class AgendaItemModel extends Model
{
    protected static string $table = 'agenda_items';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'titel', 'omschrijving', 'start_op', 'eind_op', 'type', 'gekoppeld_id',
        'locatie', 'user_id', 'aangemaakt_door_id',
    ];

    public const TYPES = [
        'afspraak' => 'Afspraak',
        'ticket' => 'Ticket',
        'verbeterpunt' => 'Verbeterpunt',
    ];

    /** @return array items van $userId, optioneel beperkt tot een periode (voor de FullCalendar-feed). */
    public static function forUser(int $userId, ?string $vanaf = null, ?string $tot = null): array
    {
        $sql = 'SELECT a.*, u.naam AS user_naam
                FROM agenda_items a
                JOIN users u ON u.id = a.user_id
                WHERE a.deleted_at IS NULL AND a.user_id = :user_id';
        $params = ['user_id' => $userId];

        if ($vanaf !== null) {
            $sql .= ' AND a.eind_op >= :vanaf';
            $params['vanaf'] = $vanaf;
        }
        if ($tot !== null) {
            $sql .= ' AND a.start_op <= :tot';
            $params['tot'] = $tot;
        }

        $sql .= ' ORDER BY a.start_op ASC';

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT a.*, u.naam AS user_naam
             FROM agenda_items a
             JOIN users u ON u.id = a.user_id
             WHERE a.id = ? AND a.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** Label van het gekoppelde ticket/verbeterpunt, voor weergave in de kalender. */
    public static function gekoppeldeTitel(string $type, ?int $id): ?string
    {
        if ($id === null) {
            return null;
        }

        $table = match ($type) {
            'ticket' => 'tickets',
            'verbeterpunt' => 'verbeterpunten',
            default => null,
        };
        if ($table === null) {
            return null;
        }

        $stmt = Database::pdo()->prepare("SELECT titel FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $titel = $stmt->fetchColumn();

        return $titel === false ? null : $titel;
    }
}
