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

    private const STATUS_LABELS = [
        'open' => 'Open',
        'in_behandeling' => 'In behandeling',
        'wacht_op_info' => 'Wacht op info',
        'opgelost' => 'Opgelost',
        'gesloten' => 'Gesloten',
    ];

    private const PRIORITEIT_LABELS = [
        'laag' => 'Laag',
        'normaal' => 'Normaal',
        'hoog' => 'Hoog',
        'kritiek' => 'Kritiek',
    ];

    protected function filterOptions(array $allItems): array
    {
        $afdelingen = array_values(array_unique(array_filter(array_column($allItems, 'afdeling_naam'))));
        sort($afdelingen);

        $behandelaars = array_values(array_unique(array_filter(array_column($allItems, 'behandelaar_naam'))));
        sort($behandelaars);

        return [
            'status' => self::STATUS_LABELS,
            'prioriteit' => self::PRIORITEIT_LABELS,
            'afdeling_naam' => array_combine($afdelingen, $afdelingen),
            'behandelaar_naam' => array_combine($behandelaars, $behandelaars),
        ];
    }

    protected function applyDefaultFilters(array $items): array
    {
        if (($_GET['status'] ?? '') === '') {
            return array_values(array_filter($items, fn (array $t) => $t['status'] !== 'opgelost'));
        }

        return $items;
    }

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

    public function export(): void
    {
        $this->requireAuth();

        $content = TicketExcel::export();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tickets-export-' . date('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    public function import(): void
    {
        $this->requireAuth();

        if (empty($_FILES['bestand']['tmp_name']) || $_FILES['bestand']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Geen geldig Excel-bestand ontvangen.';
            $this->redirect('/tickets');
        }

        $extension = strtolower(pathinfo($_FILES['bestand']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') {
            $_SESSION['flash_error'] = 'Alleen .xlsx-bestanden worden ondersteund.';
            $this->redirect('/tickets');
        }

        try {
            $result = TicketExcel::import($_FILES['bestand']['tmp_name'], (int) $this->currentUserId());
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/tickets');
        }

        $_SESSION['flash_success'] = "Import voltooid: {$result['created']} ticket(s) aangemaakt, "
            . "{$result['duplicates']} overgeslagen als duplicaat (zelfde taak + opdrachtgever bestond al), "
            . "{$result['skipped']} rij(en) overgeslagen (geen taaknaam).";
        $this->redirect('/tickets');
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
