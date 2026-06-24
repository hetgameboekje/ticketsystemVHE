<?php

namespace App\Modules\Verbeterpunt;

use App\Core\CrudController;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;
use App\Shared\Afdeling\Models\AfdelingModel;

class VerbeterpuntController extends CrudController
{
    protected string $modelClass = VerbeterpuntModel::class;
    protected string $viewDir = 'Modules/Verbeterpunt/Views/VerbeterpuntView';
    protected string $routeBase = 'verbeterpunten';
    protected string $activeModule = 'verbeterpunten';
    protected string $pageTitle = 'Verbeterpunten';

    protected function formData(): array
    {
        return ['afdelingen' => AfdelingModel::all()];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'omschrijving' => trim($post['omschrijving'] ?? ''),
            'afdeling_id' => $post['afdeling_id'] !== '' ? (int) $post['afdeling_id'] : null,
            'status' => $post['status'] ?? 'nieuw',
        ];

        if (!$isUpdate) {
            $data['status'] = 'nieuw';
            $data['ingediend_door_id'] = $this->currentUserId();
        }

        return $data;
    }
}
