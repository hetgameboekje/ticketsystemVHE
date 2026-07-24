<?php

namespace App\Modules\EmailVerwerking;

use App\Core\Controller;
use App\Modules\EmailVerwerking\Models\ImportedEmailModel;
use App\Modules\EmailVerwerking\Models\ProcessingLogModel;

/**
 * Neemt e-mails aan vanuit een extern script (bv. mailmind_intake.py op it@vhe.nl, zie
 * scripts/automation/outlook-intake/) voor de e-mail-/kennisbankverwerkingspipeline. Geen
 * sessie-auth — API-sleutel met scope 'email_import', zelfde patroon als TicketEmailIntakeController.
 */
class EmailImportController extends Controller
{
    public function store(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('email_import')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $messageId = trim($_POST['bron_message_id'] ?? '');
        $afzenderEmail = trim($_POST['afzender_email'] ?? '');
        $onderwerp = trim($_POST['onderwerp'] ?? '');
        $bodySchoon = trim($_POST['body_schoon'] ?? '');

        if ($messageId === '' || $afzenderEmail === '' || $onderwerp === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Velden "bron_message_id", "afzender_email" en "onderwerp" zijn verplicht.']);
            return;
        }

        if (ImportedEmailModel::existsByMessageId($messageId, $afzenderEmail)) {
            echo json_encode(['status' => 'duplicate']);
            return;
        }

        $ontvangenOp = trim($_POST['ontvangen_op'] ?? '');

        $id = ImportedEmailModel::create([
            'bron_message_id' => $messageId,
            'afzender_email' => $afzenderEmail,
            'afzender_naam' => trim($_POST['afzender_naam'] ?? '') ?: null,
            'onderwerp' => $onderwerp,
            'body_ruw' => $_POST['body_ruw'] ?? '',
            'body_schoon' => $bodySchoon !== '' ? $bodySchoon : ($_POST['body_ruw'] ?? ''),
            'ontvangen_at' => $ontvangenOp !== '' ? date('Y-m-d H:i:s', strtotime($ontvangenOp)) : null,
            'status' => 'stored',
        ]);

        ProcessingLogModel::create([
            'imported_email_id' => $id,
            'stap' => 'opgeslagen',
            'status' => 'ok',
            'bericht' => 'Opgeslagen via e-mailintake-script.',
        ]);

        http_response_code(201);
        echo json_encode(['status' => 'created', 'id' => $id]);
    }
}
