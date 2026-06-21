<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\Reflectie;

class ReflectieController extends CrudController
{
    protected string $modelClass = Reflectie::class;
    protected string $viewDir = 'reflecties';
    protected string $routeBase = 'reflecties';
    protected string $activeModule = 'reflecties';
    protected string $pageTitle = 'Reflectie';

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'periode' => trim($post['periode'] ?? ''),
            'inhoud' => trim($post['inhoud'] ?? ''),
        ];

        if (!$isUpdate) {
            $data['gebruiker_id'] = $this->currentUserId();
        }

        return $data;
    }
}
