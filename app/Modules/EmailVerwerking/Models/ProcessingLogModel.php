<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

/** Audit-trail per pipelinestap (ontvangen/parsed/analyse/concept/review/publicatie), zowel per e-mail als batch-breed. */
class ProcessingLogModel extends Model
{
    protected static string $table = 'processing_logs';
    protected static array $fillable = ['imported_email_id', 'stap', 'status', 'bericht'];

    public static function forEmail(int $importedEmailId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM processing_logs WHERE imported_email_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$importedEmailId]);
        return $stmt->fetchAll();
    }

    /** Meest recente fouten over alle e-mails heen, voor het "Foutmeldingen"-blok op het dashboard. */
    public static function recenteFouten(int $limiet = 20): array
    {
        $stmt = Database::pdo()->query(
            "SELECT l.*, e.onderwerp, e.afzender_email
             FROM processing_logs l
             LEFT JOIN imported_emails e ON e.id = l.imported_email_id
             WHERE l.status = 'fout'
             ORDER BY l.created_at DESC
             LIMIT " . (int) $limiet
        );
        return $stmt->fetchAll();
    }
}
