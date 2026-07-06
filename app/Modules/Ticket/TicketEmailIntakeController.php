<?php

namespace App\Modules\Ticket;

use App\Core\Controller;
use App\Modules\Ticket\Models\TicketModel;

/**
 * Neemt tickets aan vanuit een extern script (bv. een Outlook-macro op it@vhe.nl) i.p.v. via de
 * ingelogde UI. Geen sessie-auth — authenticatie gaat via een gedeeld geheim (zie config
 * 'ticketEmailIntakeApiKey'), vergelijkbaar met een webhook.
 */
class TicketEmailIntakeController extends Controller
{
    private const DEDUPE_DAGEN = 30;

    public function store(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftGeldigeApiKey()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige of ontbrekende API-key.']);
            return;
        }

        $afzender = trim($_POST['afzender'] ?? '');
        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');

        if ($afzender === '' || $titel === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Velden "afzender" en "titel" zijn verplicht.']);
            return;
        }

        if (TicketModel::existsRecentByAfzenderEnTitel($titel, $afzender, self::DEDUPE_DAGEN)) {
            echo json_encode([
                'status' => 'duplicate',
                'message' => "Al een ticket van \"{$afzender}\" met deze titel in de afgelopen " . self::DEDUPE_DAGEN . ' dagen — overgeslagen.',
            ]);
            return;
        }

        $id = TicketModel::create([
            'titel' => $titel,
            'omschrijving' => $omschrijving !== '' ? $omschrijving : '(geen omschrijving meegegeven)',
            'opdrachtgever_naam' => $afzender,
            'prioriteit' => 'normaal',
            'impact' => 'Normaal',
            'status' => 'open',
        ]);

        http_response_code(201);
        echo json_encode(['status' => 'created', 'id' => $id]);
    }

    private function heeftGeldigeApiKey(): bool
    {
        $config = require APP_ROOT . '/config/config.php';
        $verwacht = $config['ticketEmailIntakeApiKey'];

        if ($verwacht === '') {
            return false;
        }

        $meegegeven = $_SERVER['HTTP_X_API_KEY'] ?? ($_POST['api_key'] ?? '');

        return is_string($meegegeven) && hash_equals($verwacht, $meegegeven);
    }
}
