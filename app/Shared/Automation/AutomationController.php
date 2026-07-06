<?php

namespace App\Shared\Automation;

use App\Core\Controller;
use App\Modules\Ticket\TicketReminderService;
use App\Shared\Mail\EmailQueueProcessor;

/**
 * Achtergrondtaken-endpoints, bedoeld voor een externe scheduler (Taakplanner/cron) i.p.v. de UI.
 * Geen sessie-auth — authenticatie gaat via een gedeeld geheim, zelfde patroon als
 * TicketEmailIntakeController.
 */
class AutomationController extends Controller
{
    public function emailQueueVerwerken(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftGeldigeApiKey()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige of ontbrekende API-key.']);
            return;
        }

        echo json_encode(['status' => 'ok'] + EmailQueueProcessor::verwerk());
    }

    public function ticketHerinneringenGenereren(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftGeldigeApiKey()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige of ontbrekende API-key.']);
            return;
        }

        echo json_encode(['status' => 'ok'] + TicketReminderService::genereer());
    }

    private function heeftGeldigeApiKey(): bool
    {
        $config = require APP_ROOT . '/config/config.php';
        $verwacht = $config['automationApiKey'];

        if ($verwacht === '') {
            return false;
        }

        $meegegeven = $_SERVER['HTTP_X_API_KEY'] ?? ($_POST['api_key'] ?? '');

        return is_string($meegegeven) && hash_equals($verwacht, $meegegeven);
    }
}
