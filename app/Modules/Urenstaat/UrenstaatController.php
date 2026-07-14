<?php

namespace App\Modules\Urenstaat;

use App\Core\CrudController;
use App\Modules\Beheer\Models\LocatieModel;
use App\Modules\Urenstaat\Models\UrenstaatModel;

class UrenstaatController extends CrudController
{
    protected string $modelClass = UrenstaatModel::class;
    protected string $viewDir = 'Modules/Urenstaat/Views/UrenstaatView';
    protected string $routeBase = 'urenstaat';
    protected string $activeModule = 'urenstaat';
    protected string $pageTitle = 'Urenstaat';
    protected ?string $searchColumn = 'omschrijving';

    protected function scopeAllowed(array $item): bool
    {
        if (($this->currentUser()['rol'] ?? '') === 'admin') {
            return true;
        }

        return (int) ($item['user_id'] ?? 0) === (int) $this->currentUserId();
    }

    protected function extraViewData(array $allItems): array
    {
        $openDagen = UrenstaatModel::allOpenForUser((int) $this->currentUserId());

        return [
            'open' => $openDagen === [] ? null : $openDagen[count($openDagen) - 1],
            'openDagen' => $openDagen,
        ];
    }

    public function starten(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $userId = (int) $this->currentUserId();
        if (UrenstaatModel::openForUser($userId) === null) {
            UrenstaatModel::create([
                'user_id' => $userId,
                'datum' => date('Y-m-d'),
                'start_tijd' => date('H:i:s'),
                'eind_tijd' => null,
            ]);
        }

        $this->redirect('/urenstaat');
    }

    public function stoppen(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $open = UrenstaatModel::openForUser((int) $this->currentUserId());
        if ($open !== null) {
            UrenstaatModel::update((int) $open['id'], ['eind_tijd' => date('H:i:s')]);
        }

        $this->redirect('/urenstaat');
    }

    public function stoppenAjax(int $id): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');
        header('Content-Type: application/json');

        $item = UrenstaatModel::find($id);
        $userId = (int) $this->currentUserId();

        if ($item === null || (int) $item['user_id'] !== $userId || $item['eind_tijd'] !== null) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Niet gevonden of al afgesloten.']);
            return;
        }

        UrenstaatModel::update($id, ['eind_tijd' => date('H:i:s')]);
        echo json_encode(['success' => true, 'id' => $id]);
    }

    protected function formData(): array
    {
        return [
            'locaties' => LocatieModel::visibleForUser((int) $this->currentUserId()),
        ];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $locatieId = (int) ($post['locatie_id'] ?? 0);
        $toegestaneLocaties = array_column(LocatieModel::visibleForUser((int) $this->currentUserId()), 'id');

        $data = [
            'user_id' => $this->currentUserId(),
            'locatie_id' => in_array($locatieId, $toegestaneLocaties, false) ? $locatieId : null,
            'datum' => $post['datum'] ?? null,
            'start_tijd' => $post['start_tijd'] ?? null,
            'eind_tijd' => $post['eind_tijd'] ?? null,
            'omschrijving' => trim((string) ($post['omschrijving'] ?? '')) ?: null,
        ];

        if (!$isUpdate) {
            return $data;
        }

        unset($data['user_id']);
        return $data;
    }
}
