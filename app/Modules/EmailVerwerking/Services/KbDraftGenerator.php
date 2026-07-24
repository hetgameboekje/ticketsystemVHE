<?php

namespace App\Modules\EmailVerwerking\Services;

use App\Modules\EmailVerwerking\Models\KbArticleDraftModel;
use App\Modules\EmailVerwerking\Models\KbArticleSourceModel;

/**
 * Zet een AI-analyse (zie AiAnalysisService) om in een kennisbankconcept, en koppelt de brontmail.
 * Hergebruikt een bestaand concept met een gelijkende titel/categorie i.p.v. een duplicaat aan te
 * maken — dat is de "detectie van terugkerende problemen" uit het implementatieplan.
 */
class KbDraftGenerator
{
    public function verwerk(int $importedEmailId, array $analyse): int
    {
        $titel = (string) $analyse['voorgestelde_titel'];
        $categorie = (string) $analyse['categorie'];

        $bestaand = KbArticleDraftModel::vindOpTitelGelijkenis($titel, $categorie);
        $tags = is_array($analyse['tags']) ? implode(', ', $analyse['tags']) : (string) $analyse['tags'];

        if ($bestaand !== null) {
            $draftId = (int) $bestaand['id'];
        } else {
            $draftId = KbArticleDraftModel::create([
                'titel' => $titel,
                'categorie' => $categorie,
                'subcategorie' => $analyse['subcategorie'] ?? null,
                'samenvatting' => $analyse['samenvatting'] ?? null,
                'probleem' => $analyse['probleem'] ?? null,
                'oplossing' => $analyse['oplossing_suggestie'] ?? null,
                'tags' => $tags !== '' ? $tags : null,
                'status' => 'draft_created',
                'confidence' => $analyse['confidence'] ?? null,
            ]);
        }

        KbArticleSourceModel::koppel($draftId, $importedEmailId);

        return $draftId;
    }
}
