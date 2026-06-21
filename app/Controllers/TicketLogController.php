<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;

class TicketLogController extends Controller
{
    public function store(int $ticketId): void
    {
        $this->requireAuth();

        $ticket = Ticket::find($ticketId);
        if ($ticket === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $opmerking = trim($_POST['opmerking'] ?? '');
        $nieuweStatus = $_POST['status'] ?? '';
        $statusGewijzigd = $nieuweStatus !== '' && $nieuweStatus !== $ticket['status'];

        if ($opmerking !== '' || $statusGewijzigd) {
            TicketLog::create([
                'ticket_id' => $ticketId,
                'user_id' => $this->currentUserId(),
                'opmerking' => $opmerking !== '' ? $opmerking : 'Status bijgewerkt.',
                'status_van' => $statusGewijzigd ? $ticket['status'] : null,
                'status_naar' => $statusGewijzigd ? $nieuweStatus : null,
            ]);
        }

        if ($statusGewijzigd) {
            Ticket::update($ticketId, ['status' => $nieuweStatus]);
        }

        $this->redirect("/tickets/{$ticketId}");
    }
}
