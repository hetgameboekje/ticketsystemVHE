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

        $opmerking = trim($_POST['opmerking'] ?? '');
        $nieuweStatus = $_POST['status'] ?? '';
        $statusGewijzigd = $nieuweStatus !== '' && $nieuweStatus !== $ticket['status'];

        if ($opmerking !== '' || $statusGewijzigd) {
            TicketLogModel::create([
                'ticket_id' => $ticketId,
                'user_id' => $this->currentUserId(),
                'opmerking' => $opmerking !== '' ? $opmerking : 'Status bijgewerkt.',
                'status_van' => $statusGewijzigd ? $ticket['status'] : null,
                'status_naar' => $statusGewijzigd ? $nieuweStatus : null,
            ]);
        }

        if ($statusGewijzigd) {
            TicketModel::update($ticketId, ['status' => $nieuweStatus]);
        }

        $this->redirect("/tickets/{$ticketId}");
    }
}
