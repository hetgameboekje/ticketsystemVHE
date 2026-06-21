<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\Afdeling;
use App\Models\Medewerker;

class MedewerkerController extends CrudController
{
    protected string $modelClass = Medewerker::class;
    protected string $viewDir = 'medewerkers';
    protected string $routeBase = 'medewerkers';
    protected string $activeModule = 'medewerkers';
    protected string $pageTitle = 'Medewerkers';

    protected function formData(): array
    {
        return ['afdelingen' => Afdeling::all()];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        return [
            'voornaam' => trim($post['voornaam'] ?? ''),
            'achternaam' => trim($post['achternaam'] ?? ''),
            'email' => trim($post['email'] ?? ''),
            'telefoon' => trim($post['telefoon'] ?? ''),
            'functie' => trim($post['functie'] ?? ''),
            'afdeling_id' => $post['afdeling_id'] !== '' ? (int) $post['afdeling_id'] : null,
            'startdatum' => $post['startdatum'] !== '' ? $post['startdatum'] : null,
            'status' => $post['status'] ?? 'actief',
        ];
    }
}
