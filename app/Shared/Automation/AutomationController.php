<?php

namespace App\Shared\Automation;

use App\Core\Controller;
use App\Core\DatabaseDump;
use App\Modules\Ticket\TicketReminderService;
use App\Shared\Auth\Models\LoginAttemptModel;
use App\Shared\Log\Models\PaginaBezoekModel;
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

    public function logsOpschonen(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('log_opschonen')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $config = require APP_ROOT . '/config/config.php';
        $dagen = (int) $config['logRetentieDagen'];

        echo json_encode([
            'status' => 'ok',
            'retentie_dagen' => $dagen,
            'paginabezoeken_verwijderd' => PaginaBezoekModel::verwijderOuderDan($dagen),
            'login_attempts_verwijderd' => LoginAttemptModel::verwijderOuderDan($dagen),
        ]);
    }

    /**
     * Levert een volledige SQL-dump van de live database (alle tabellen, schema + data), bedoeld
     * om vanuit een lokale dev-omgeving opgehaald te worden (zie scripts/dev-tools/dev-tools.ps1). Bevat
     * ongefilterde data, inclusief wachtwoordhashes — de scope 'database_export' moet dus alleen
     * aan sleutels toegekend worden die je zelf beheert, niet aan externe scripts.
     */
    public function databaseExport(): void
    {
        if (!$this->heeftApiSleutelMetScope('database_export')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $sql = DatabaseDump::generate();

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="database-export-' . date('Y-m-d_His') . '.sql"');
        header('Content-Length: ' . strlen($sql));
        echo $sql;
    }
}
