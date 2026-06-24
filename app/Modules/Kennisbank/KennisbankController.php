<?php

namespace App\Modules\Kennisbank;

use App\Core\CrudController;
use App\Modules\Kennisbank\Models\KennisbankModel;

class KennisbankController extends CrudController
{
    protected string $modelClass = KennisbankModel::class;
    protected string $viewDir = 'Modules/Kennisbank/Views/KennisbankView';
    protected string $routeBase = 'kennisbank';
    protected string $activeModule = 'kennisbank';
    protected string $pageTitle = 'Kennisbank';

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
