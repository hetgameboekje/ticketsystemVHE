<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\Afdeling;
use App\Models\HardwareUitgave;

class HardwareUitgaveController extends CrudController
{
    protected string $modelClass = HardwareUitgave::class;
    protected string $viewDir = 'hardware';
    protected string $routeBase = 'hardware-uitgaven';
    protected string $activeModule = 'hardware';
    protected string $pageTitle = 'Uitgaven hardware';

    protected function formData(): array
    {
        return ['afdelingen' => Afdeling::all()];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $data = [
            'omschrijving' => trim($post['omschrijving'] ?? ''),
            'leverancier' => trim($post['leverancier'] ?? ''),
            'bedrag' => $post['bedrag'] !== '' ? (float) $post['bedrag'] : 0,
            'aankoopdatum' => $post['aankoopdatum'] !== '' ? $post['aankoopdatum'] : null,
            'afdeling_id' => $post['afdeling_id'] !== '' ? (int) $post['afdeling_id'] : null,
            'status' => $post['status'] ?? 'aangevraagd',
        ];

        if (!$isUpdate) {
            $data['status'] = 'aangevraagd';
            $data['aangevraagd_door_id'] = $this->currentUserId();
        }

        return $data;
    }
}
