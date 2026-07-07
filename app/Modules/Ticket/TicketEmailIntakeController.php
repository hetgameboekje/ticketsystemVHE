<?php

namespace App\Modules\Ticket;

use App\Core\Controller;
use App\Modules\Ticket\Models\TicketLogModel;
use App\Modules\Ticket\Models\TicketModel;
use App\Shared\User\Models\UserModel;

/**
 * Neemt tickets aan vanuit een extern script (bv. het Outlook/pywin32-script op it@vhe.nl,
 * zie automation/outlook-intake/) i.p.v. via de ingelogde UI. Geen sessie-auth — authenticatie
 * gaat via een API-sleutel met de juiste scope (zie App\Shared\ApiKey\Models\ApiKeyModel en
 * Beheer > API-sleutels), vergelijkbaar met een webhook.
 */
class TicketEmailIntakeController extends Controller
{
    private const DEDUPE_DAGEN = 30;

    public function store(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('ticket_intake')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $afzender = trim($_POST['afzender'] ?? '');
        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');
        $behandelaarHint = trim($_POST['behandelaar_hint'] ?? '');

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
            'behandelaar_id' => $behandelaarHint !== '' ? UserModel::findIdByNaamBevat($behandelaarHint) : null,
        ]);

        http_response_code(201);
        echo json_encode(['status' => 'created', 'id' => $id]);
    }

    /**
     * Neemt ACA-case-updatemails aan (zie Python/pywin32-script op it@vhe.nl). Het CAS-nummer
     * (bv. "CAS-109512-R6Z2W3") blijft constant over de levensduur van een case, terwijl het
     * ACA-nummer per update-mail wijzigt — het CAS-nummer is dus de sleutel om updates aan
     * hetzelfde ticket te koppelen (opgeslagen in escalatie_nummer, net als handmatige escalaties).
     */
    public function storeAcaUpdate(): void
    {
        header('Content-Type: application/json');

        if (!$this->heeftApiSleutelMetScope('aca_intake')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Ongeldige, ontbrekende of onvoldoende gemachtigde API-key.']);
            return;
        }

        $casNummer = trim($_POST['cas_nummer'] ?? '');
        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');
        $acaNummer = trim($_POST['aca_nummer'] ?? '');
        $actie = strtolower(trim($_POST['actie'] ?? ''));
        $behandelaarHint = trim($_POST['behandelaar_hint'] ?? '');

        if ($casNummer === '' || $titel === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Velden "cas_nummer" en "titel" zijn verplicht.']);
            return;
        }

        $logRegel = $acaNummer !== '' ? "ACA:{$acaNummer} — {$omschrijving}" : $omschrijving;
        $ticket = TicketModel::findByEscalatieNummer($casNummer);

        if ($ticket === null) {
            $id = TicketModel::create([
                'titel' => $titel,
                'omschrijving' => $omschrijving !== '' ? $omschrijving : '(geen omschrijving meegegeven)',
                'opdrachtgever_naam' => 'ACA',
                'prioriteit' => 'normaal',
                'impact' => 'Normaal',
                'status' => 'open',
                'escalatie_nummer' => $casNummer,
                'escalatie_instantie' => 'ACA',
                'behandelaar_id' => $behandelaarHint !== '' ? UserModel::findIdByNaamBevat($behandelaarHint) : null,
            ]);

            http_response_code(201);
            echo json_encode(['status' => 'created', 'id' => $id]);
            return;
        }

        $nieuweStatus = $actie === 'afmelding' ? 'afgehandeld' : null;

        TicketLogModel::create([
            'ticket_id' => $ticket['id'],
            'user_id' => null,
            'opmerking' => $logRegel !== '' ? $logRegel : 'ACA-update ontvangen.',
            'status_van' => $nieuweStatus !== null ? $ticket['status'] : null,
            'status_naar' => $nieuweStatus,
        ]);

        if ($nieuweStatus !== null) {
            TicketModel::update($ticket['id'], ['status' => $nieuweStatus]);
        }

        echo json_encode(['status' => 'updated', 'id' => $ticket['id']]);
    }
}
