<?php

namespace App\Modules\Ticket;

use App\Core\Controller;
use App\Modules\Ticket\Models\TicketLogModel;
use App\Modules\Ticket\Models\TicketModel;

class TicketLogController extends Controller
{
    public function store(int $ticketId): void
    {
        $this->requirePermission('tickets', 'schrijven');

        $ticket = TicketModel::find($ticketId);
        if ($ticket === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $titel = trim($_POST['titel'] ?? '');
        $opmerking = trim($_POST['opmerking'] ?? '');
        // Titel is verplicht voor een opmerking, omschrijving niet.
        $opmerkingGeldig = $titel !== '';
        $nieuweStatus = $_POST['status'] ?? '';
        $statusGewijzigd = $nieuweStatus !== '' && $nieuweStatus !== $ticket['status'];

        if ($opmerkingGeldig || $statusGewijzigd) {
            TicketLogModel::create([
                'ticket_id' => $ticketId,
                'user_id' => $this->currentUserId(),
                'titel' => $opmerkingGeldig ? $titel : null,
                'opmerking' => $opmerkingGeldig ? $opmerking : 'Status bijgewerkt.',
                'status_van' => $statusGewijzigd ? $ticket['status'] : null,
                'status_naar' => $statusGewijzigd ? $nieuweStatus : null,
            ]);
        }

        // Escalatienummer/-instantie staan op dezelfde pagina in een eigen kaart, maar delen dit
        // formulier (form="ticketLogForm") — zo gaat er niets verloren als je die invult en vervolgens
        // op "Opslaan" (opmerking) of "Status bijwerken" klikt i.p.v. op de escalatie-knop.
        $escalatieData = TicketModel::alleenGewijzigdeVelden($ticket, [
            'escalatie_nummer' => trim($_POST['escalatie_nummer'] ?? ''),
            'escalatie_instantie' => trim($_POST['escalatie_instantie'] ?? ''),
        ]);

        $ticketUpdate = $escalatieData;
        if ($statusGewijzigd) {
            $ticketUpdate['status'] = $nieuweStatus;
        }

        if ($ticketUpdate !== []) {
            TicketModel::update($ticketId, $ticketUpdate);
        }

        $this->redirect("/tickets/{$ticketId}");
    }
}
