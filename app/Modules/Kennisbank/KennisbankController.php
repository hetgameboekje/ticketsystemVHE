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
        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'categorie' => trim($post['categorie'] ?? '') ?: 'Algemeen',
            'inhoud' => trim($post['inhoud'] ?? ''),
        ];

        if (!$isUpdate) {
            $data['auteur_id'] = $this->currentUserId();
        }

        return $data;
    }
}
