<?php

namespace App\Modules\Kennisbank;

use App\Core\CrudController;
use App\Modules\Kennisbank\Models\KennisbankLogModel;
use App\Modules\Kennisbank\Models\KennisbankModel;

class KennisbankController extends CrudController
{
    protected string $modelClass = KennisbankModel::class;
    protected string $viewDir = 'Modules/Kennisbank/Views/KennisbankView';
    protected string $routeBase = 'kennisbank';
    protected string $activeModule = 'kennisbank';
    protected string $pageTitle = 'Kennisbank';

    private const QUICK_ACTION_TYPES = ['powershell' => 'PowerShell', 'batch' => 'Batch', 'bash' => 'Bash', 'overig' => 'Overig'];

    protected function formData(): array
    {
        return ['quickActionTypes' => self::QUICK_ACTION_TYPES];
    }

    public function show(int $id): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $item = KennisbankModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/show", [
            'item' => $item,
            'logs' => KennisbankLogModel::forArtikel($id),
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $quickActionEnabled = ($post['quick_action_enabled'] ?? '0') === '1';
        $quickActionScript = $quickActionEnabled ? trim($post['quick_action_script'] ?? '') : '';

        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'categorie' => trim($post['categorie'] ?? '') ?: 'Algemeen',
            'inhoud' => trim($post['inhoud'] ?? ''),
            'quick_action_type' => $quickActionScript !== ''
                ? (in_array($post['quick_action_type'] ?? '', array_keys(self::QUICK_ACTION_TYPES), true) ? $post['quick_action_type'] : 'overig')
                : null,
            'quick_action_omschrijving' => $quickActionScript !== '' ? (trim($post['quick_action_omschrijving'] ?? '') ?: null) : null,
            'quick_action_script' => $quickActionScript !== '' ? $quickActionScript : null,
        ];

        if (!$isUpdate) {
            $data['auteur_id'] = $this->currentUserId();
        }

        return $data;
    }
}
