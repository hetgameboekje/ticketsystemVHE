<?php

namespace App\Controllers;

use App\Core\CrudController;
use App\Models\KennisbankArtikel;

class KennisbankController extends CrudController
{
    protected string $modelClass = KennisbankArtikel::class;
    protected string $viewDir = 'kennisbank';
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
