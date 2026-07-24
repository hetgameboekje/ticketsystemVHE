<?php

namespace App\Modules\EmailVerwerking;

use App\Core\Controller;
use App\Modules\EmailVerwerking\Models\EmailAiAnalysisModel;
use App\Modules\EmailVerwerking\Models\ImportedEmailModel;
use App\Modules\EmailVerwerking\Models\ProcessingLogModel;
use App\Modules\EmailVerwerking\Services\AiAnalysisService;
use App\Modules\EmailVerwerking\Services\KbDraftGenerator;

/**
 * Achtergrondtaak voor de e-mail-/kennisbankverwerkingspipeline, bedoeld voor dezelfde externe
 * scheduler (Taakplanner) als App\Shared\Automation\AutomationController: pakt e-mails met
 * status 'stored' op, laat ze AI-classificeren en zet ze om in (of koppelt ze aan) een
 * kennisbankconcept. Geen sessie-auth — API-sleutel met scope 'email_analysis'.
 */
class EmailAnalysisController extends Controller
{
    private const BATCHGROOTTE = 20;

    public function verwerken(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('email_analysis')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $aiService = new AiAnalysisService();
        $draftGenerator = new KbDraftGenerator();

        $verwerkt = 0;
        $mislukt = 0;

        foreach (ImportedEmailModel::metStatus('stored', self::BATCHGROOTTE) as $email) {
            try {
                $analyse = $aiService->analyseer($email['onderwerp'], $email['body_schoon']);

                EmailAiAnalysisModel::create([
                    'imported_email_id' => $email['id'],
                    'model_versie' => $analyse['model_versie'],
                    'herkend_onderwerp' => $analyse['onderwerp'],
                    'categorie' => $analyse['categorie'],
                    'subcategorie' => $analyse['subcategorie'] ?? null,
                    'sentiment' => $analyse['sentiment'],
                    'urgentie' => $analyse['urgentie'],
                    'samenvatting' => $analyse['samenvatting'],
                    'probleem' => $analyse['probleem'],
                    'oplossing_suggestie' => $analyse['oplossing_suggestie'],
                    'voorgestelde_titel' => $analyse['voorgestelde_titel'],
                    'tags' => is_array($analyse['tags']) ? implode(', ', $analyse['tags']) : $analyse['tags'],
                    'confidence' => $analyse['confidence'],
                    'mens_review_aanbevolen' => !empty($analyse['mens_review_aanbevolen']) ? 1 : 0,
                    'ruwe_response' => $analyse['ruwe_response'],
                ]);

                ImportedEmailModel::update((int) $email['id'], ['status' => 'analyzed']);
                ProcessingLogModel::create([
                    'imported_email_id' => $email['id'], 'stap' => 'analyse', 'status' => 'ok',
                    'bericht' => "Geclassificeerd als \"{$analyse['categorie']}\" (confidence {$analyse['confidence']}).",
                ]);

                $draftId = $draftGenerator->verwerk((int) $email['id'], $analyse);
                ImportedEmailModel::update((int) $email['id'], ['status' => 'draft_created']);
                ProcessingLogModel::create([
                    'imported_email_id' => $email['id'], 'stap' => 'concept', 'status' => 'ok',
                    'bericht' => "Gekoppeld aan conceptartikel #{$draftId}.",
                ]);

                $verwerkt++;
            } catch (\Throwable $e) {
                ImportedEmailModel::update((int) $email['id'], ['status' => 'failed', 'laatste_fout' => $e->getMessage()]);
                ProcessingLogModel::create([
                    'imported_email_id' => $email['id'], 'stap' => 'analyse', 'status' => 'fout',
                    'bericht' => $e->getMessage(),
                ]);
                $mislukt++;
            }
        }

        echo json_encode(['status' => 'ok', 'verwerkt' => $verwerkt, 'mislukt' => $mislukt]);
    }
}
