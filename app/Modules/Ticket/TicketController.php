<?php

namespace App\Modules\Ticket;

use App\Core\CrudController;
use App\Modules\Ticket\Models\TicketLogModel;
use App\Modules\Ticket\Models\TicketModel;
use App\Shared\Afdeling\Models\AfdelingModel;
use App\Shared\User\Models\UserModel;

class TicketController extends CrudController
{
    protected string $modelClass = TicketModel::class;
    protected string $viewDir = 'Modules/Ticket/Views/TicketView';
    protected string $routeBase = 'tickets';
    protected string $activeModule = 'tickets';
    protected string $pageTitle = 'Tickets';

    public function show(int $id): void
    {
        $this->requireAuth();
        $item = TicketModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/show", [
            'item' => $item,
            'logs' => TicketLogModel::forTicket($id),
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    protected function formData(): array
    {
        return [
            'afdelingen' => AfdelingModel::all(),
            'gebruikers' => UserModel::all('naam ASC'),
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
            'schatting_minuten' => $post['schatting_minuten'] !== '' ? (int) $post['schatting_minuten'] : null,
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
