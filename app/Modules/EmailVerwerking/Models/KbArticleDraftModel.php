<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;
use App\Modules\Kennisbank\Models\KennisbankModel;

class KbArticleDraftModel extends Model
{
    protected static string $table = 'kb_article_drafts';
    protected static array $fillable = [
        'titel', 'categorie', 'subcategorie', 'samenvatting', 'probleem', 'oplossing', 'stappenplan',
        'tags', 'status', 'confidence', 'kennisbank_artikel_id', 'reviewer_id', 'versie',
    ];
    protected static bool $softDeletes = true;

    private const SELECT = "
        SELECT d.*, u.naam AS reviewer_naam,
               (SELECT COUNT(*) FROM kb_article_sources s WHERE s.kb_article_draft_id = d.id) AS aantal_bronnen
        FROM kb_article_drafts d
        LEFT JOIN users u ON u.id = d.reviewer_id
        WHERE d.deleted_at IS NULL
    ";

    public static function allWithRelations(): array
    {
        return Database::pdo()->query(self::SELECT . ' ORDER BY d.created_at DESC')->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND d.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function metStatus(string $status): array
    {
        $stmt = Database::pdo()->prepare(self::SELECT . ' AND d.status = ? ORDER BY d.created_at ASC');
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public static function telPerStatus(): array
    {
        $stmt = Database::pdo()->query(
            'SELECT status, COUNT(*) AS aantal FROM kb_article_drafts WHERE deleted_at IS NULL GROUP BY status'
        );
        return array_column($stmt->fetchAll(), 'aantal', 'status');
    }

    /**
     * Zoekt een openstaand concept met een gelijkende titel binnen dezelfde categorie — voorkomt
     * dubbele concepten voor hetzelfde terugkerende probleem. similar_text() geeft het percentage
     * overeenkomst; 60% is een pragmatische drempel (titels zijn AI-voorstellen, dus vrijwel
     * identieke problemen leveren zeer gelijkende titels op, echt andere problemen ruim minder).
     */
    public static function vindOpTitelGelijkenis(string $titel, string $categorie): ?array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM kb_article_drafts
             WHERE deleted_at IS NULL AND categorie = ? AND status IN ('draft_created', 'in_review')"
        );
        $stmt->execute([$categorie]);

        foreach ($stmt->fetchAll() as $kandidaat) {
            similar_text(mb_strtolower($titel), mb_strtolower($kandidaat['titel']), $percentage);
            if ($percentage >= 60.0) {
                return $kandidaat;
            }
        }

        return null;
    }

    /** Publiceert het concept als kennisbankartikel en koppelt het terug (zie EmailVerwerkingController::publiceren()). */
    public static function publiceer(int $id, ?int $auteurId): int
    {
        $draft = self::find($id);
        if ($draft === null) {
            throw new \RuntimeException("Conceptartikel {$id} niet gevonden.");
        }

        $artikelId = KennisbankModel::create([
            'titel' => $draft['titel'],
            'categorie' => $draft['categorie'],
            'subcategorie' => $draft['subcategorie'],
            'samenvatting' => $draft['samenvatting'],
            'tags' => $draft['tags'],
            'inhoud' => self::samengesteldeInhoud($draft),
            'auteur_id' => $auteurId,
        ]);

        self::update($id, ['status' => 'published', 'kennisbank_artikel_id' => $artikelId]);

        $stmt = Database::pdo()->prepare(
            "UPDATE imported_emails e
             JOIN kb_article_sources s ON s.imported_email_id = e.id
             SET e.status = 'published'
             WHERE s.kb_article_draft_id = ?"
        );
        $stmt->execute([$id]);

        return $artikelId;
    }

    private static function samengesteldeInhoud(array $draft): string
    {
        $delen = [];
        if (!empty($draft['probleem'])) {
            $delen[] = "## Probleem\n" . $draft['probleem'];
        }
        if (!empty($draft['oplossing'])) {
            $delen[] = "## Oplossing\n" . $draft['oplossing'];
        }
        if (!empty($draft['stappenplan'])) {
            $delen[] = "## Stappenplan\n" . $draft['stappenplan'];
        }

        return implode("\n\n", $delen) ?: (string) $draft['samenvatting'];
    }
}
