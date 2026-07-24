<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

class EmailAiAnalysisModel extends Model
{
    protected static string $table = 'email_ai_analysis';
    protected static array $fillable = [
        'imported_email_id', 'model_versie', 'herkend_onderwerp', 'categorie', 'subcategorie',
        'sentiment', 'urgentie', 'samenvatting', 'probleem', 'oplossing_suggestie',
        'voorgestelde_titel', 'tags', 'confidence', 'mens_review_aanbevolen', 'ruwe_response',
    ];

    public static function forEmail(int $importedEmailId): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM email_ai_analysis WHERE imported_email_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$importedEmailId]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function gemiddeldeConfidence(): float
    {
        $stmt = Database::pdo()->query('SELECT AVG(confidence) FROM email_ai_analysis');
        $value = $stmt->fetchColumn();
        return $value !== false && $value !== null ? round((float) $value, 2) : 0.0;
    }
}
