<?php

namespace App\Modules\Ticket;

use App\Core\Controller;
use App\Modules\Ticket\Models\TicketModel;
use App\Modules\Ticket\Models\TicketTijdModel;

class TicketTijdController extends Controller
{
    private const TOEGESTANE_BLOKKEN = [5, 10, 15, 30, 45, 60];

    public function store(int $ticketId): void
    {
        $this->requirePermission('tickets', 'schrijven');

        if (TicketModel::find($ticketId) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $minuten = (int) ($_POST['minuten'] ?? 0);

        if (in_array($minuten, self::TOEGESTANE_BLOKKEN, true)) {
            TicketTijdModel::create([
                'ticket_id' => $ticketId,
                'user_id' => $this->currentUserId(),
                'minuten' => $minuten,
            ]);
        }

        $this->redirect("/tickets/{$ticketId}");
    }
}
