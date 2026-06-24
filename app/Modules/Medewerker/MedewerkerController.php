<?php

namespace App\Modules\Medewerker;

use App\Core\CrudController;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Shared\Afdeling\Models\AfdelingModel;

class MedewerkerController extends CrudController
{
    protected string $modelClass = MedewerkerModel::class;
    protected string $viewDir = 'Modules/Medewerker/Views/MedewerkerView';
    protected string $routeBase = 'medewerkers';
    protected string $activeModule = 'medewerkers';
    protected string $pageTitle = 'Medewerkers';

    protected function formData(): array
    {
        return ['afdelingen' => AfdelingModel::all()];
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
