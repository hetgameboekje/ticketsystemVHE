<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\Afdeling;
use App\Models\Verbeterpunt;

class VerbeterpuntController extends CrudController
{
    protected string $modelClass = Verbeterpunt::class;
    protected string $viewDir = 'verbeterpunten';
    protected string $routeBase = 'verbeterpunten';
    protected string $activeModule = 'verbeterpunten';
    protected string $pageTitle = 'Verbeterpunten';

    protected function formData(): array
    {
        return ['afdelingen' => Afdeling::all()];
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
