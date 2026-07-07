<?php

namespace App\Shared\Automation;

use App\Core\Controller;
use App\Modules\Ticket\TicketReminderService;
use App\Shared\Mail\EmailQueueProcessor;

/**
 * Achtergrondtaken-endpoints, bedoeld voor een externe scheduler (Taakplanner/cron) i.p.v. de UI.
 * Geen sessie-auth — authenticatie gaat via een API-sleutel met de juiste scope, zelfde patroon
 * als TicketEmailIntakeController (zie App\Shared\ApiKey\Models\ApiKeyModel en Beheer > API-sleutels).
 */
class AutomationController extends Controller
{
    public function emailQueueVerwerken(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('email_queue')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        echo json_encode(['status' => 'ok'] + EmailQueueProcessor::verwerk());
    }

    public function ticketHerinneringenGenereren(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('ticket_herinneringen')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        echo json_encode(['status' => 'ok'] + TicketReminderService::genereer());
    }
}
