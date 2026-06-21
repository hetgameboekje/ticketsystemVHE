<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\Afdeling;
use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\User;

class TicketController extends CrudController
{
    protected string $modelClass = Ticket::class;
    protected string $viewDir = 'tickets';
    protected string $routeBase = 'tickets';
    protected string $activeModule = 'tickets';
    protected string $pageTitle = 'Tickets';

    public function show(int $id): void
    {
        $this->requireAuth();
        $item = Ticket::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render('tickets/show', [
            'item' => $item,
            'logs' => TicketLog::forTicket($id),
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    protected function formData(): array
    {
        return [
            'afdelingen' => Afdeling::all(),
            'gebruikers' => User::all('naam ASC'),
        ];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'omschrijving' => trim($post['omschrijving'] ?? ''),
            'opdrachtgever_naam' => trim($post['opdrachtgever_naam'] ?? ''),
            'afdeling_id' => $post['afdeling_id'] !== '' ? (int) $post['afdeling_id'] : null,
            'prioriteit' => $post['prioriteit'] ?? 'normaal',
            'impact' => $post['impact'] ?? 'Normaal',
            'schatting_uren' => $post['schatting_uren'] !== '' ? (float) $post['schatting_uren'] : null,
            'deadline' => $post['deadline'] !== '' ? $post['deadline'] : null,
            'behandelaar_id' => !empty($post['behandelaar_id']) ? (int) $post['behandelaar_id'] : null,
        ];

        if (!$isUpdate) {
            $data['status'] = 'open';
            $data['aangemaakt_door_id'] = $this->currentUserId();
        }

        return $data;
    }
}
