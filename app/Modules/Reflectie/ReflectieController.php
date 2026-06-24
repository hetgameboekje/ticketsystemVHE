<?php

namespace App\Modules\Reflectie;

use App\Core\CrudController;
use App\Modules\Reflectie\Models\ReflectieModel;

class ReflectieController extends CrudController
{
    protected string $modelClass = ReflectieModel::class;
    protected string $viewDir = 'Modules/Reflectie/Views/ReflectieView';
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
