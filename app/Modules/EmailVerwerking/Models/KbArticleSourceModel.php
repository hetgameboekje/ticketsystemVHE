<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

class KbArticleSourceModel extends Model
{
    protected static string $table = 'kb_article_sources';
    protected static array $fillable = ['kb_article_draft_id', 'imported_email_id'];

    /** Koppelt een e-mail aan een concept, tenzij die koppeling al bestaat (idempotent bij een tweede analyse-run). */
    public static function koppel(int $draftId, int $importedEmailId): void
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id FROM kb_article_sources WHERE kb_article_draft_id = ? AND imported_email_id = ?'
        );
        $stmt->execute([$draftId, $importedEmailId]);
        if ($stmt->fetchColumn() !== false) {
            return;
        }

        self::create(['kb_article_draft_id' => $draftId, 'imported_email_id' => $importedEmailId]);
    }

    /** Vindt het (eerste) conceptartikel waaraan deze e-mail als bron is gekoppeld, voor de link vanaf de e-maildetailpagina. */
    public static function draftForEmail(int $importedEmailId): ?array
    {
        $stmt = Database::pdo()->prepare("
            SELECT d.* FROM kb_article_sources s
            JOIN kb_article_drafts d ON d.id = s.kb_article_draft_id
            WHERE s.imported_email_id = ? AND d.deleted_at IS NULL
            ORDER BY s.id DESC LIMIT 1
        ");
        $stmt->execute([$importedEmailId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function forDraft(int $draftId): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT s.*, e.onderwerp, e.afzender_naam, e.afzender_email, e.created_at AS email_created_at
            FROM kb_article_sources s
            JOIN imported_emails e ON e.id = s.imported_email_id
            WHERE s.kb_article_draft_id = ?
            ORDER BY e.created_at ASC
        ");
        $stmt->execute([$draftId]);
        return $stmt->fetchAll();
    }
}
