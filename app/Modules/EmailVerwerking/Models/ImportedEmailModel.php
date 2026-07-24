<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

class ImportedEmailModel extends Model
{
    protected static string $table = 'imported_emails';
    protected static array $fillable = [
        'batch_id', 'bron_message_id', 'afzender_email', 'afzender_naam', 'onderwerp',
        'body_ruw', 'body_schoon', 'ontvangen_at', 'status', 'laatste_fout',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT e.*, a.categorie AS ai_categorie, a.urgentie AS ai_urgentie, a.confidence AS ai_confidence,
               a.voorgestelde_titel AS ai_voorgestelde_titel
        FROM imported_emails e
        LEFT JOIN email_ai_analysis a ON a.imported_email_id = e.id
        WHERE e.deleted_at IS NULL
    ";

    /** Dedupe: dezelfde afzender + hetzelfde bron-message-id is altijd dezelfde mail (retry van het intake-script). */
    public static function existsByMessageId(string $messageId, string $afzenderEmail): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id FROM imported_emails WHERE bron_message_id = ? AND afzender_email = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$messageId, $afzenderEmail]);
        return $stmt->fetchColumn() !== false;
    }

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY e.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND e.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<int, array<string, mixed>> */
    public static function metStatus(string $status, int $limiet = 20): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM imported_emails WHERE status = ? AND deleted_at IS NULL ORDER BY created_at ASC LIMIT ' . (int) $limiet
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    /** @return array<string, int> status => aantal, voor de dashboard-KPI's. */
    public static function telPerStatus(): array
    {
        $stmt = Database::pdo()->query(
            'SELECT status, COUNT(*) AS aantal FROM imported_emails WHERE deleted_at IS NULL GROUP BY status'
        );
        return array_column($stmt->fetchAll(), 'aantal', 'status');
    }

    public static function countVandaag(): int
    {
        $stmt = Database::pdo()->query(
            'SELECT COUNT(*) FROM imported_emails WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE()'
        );
        return (int) $stmt->fetchColumn();
    }
}
