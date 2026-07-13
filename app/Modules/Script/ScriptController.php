<?php

namespace App\Modules\Script;

use App\Core\CrudController;
use App\Modules\Script\Models\ScriptModel;

class ScriptController extends CrudController
{
    protected string $modelClass = ScriptModel::class;
    protected string $viewDir = 'Modules/Script/Views/ScriptView';
    protected string $routeBase = 'scripts';
    protected string $activeModule = 'scripts';
    protected string $pageTitle = 'Scripts';
    protected ?string $searchColumn = 'titel';

    private const SCRIPT_TYPES = ['powershell' => 'PowerShell', 'batch' => 'Batch', 'bash' => 'Bash', 'overig' => 'Overig'];

    protected function formData(): array
    {
        return ['scriptTypes' => self::SCRIPT_TYPES];
    }

    public function show(int $id): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $item = ScriptModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/show", [
            'item' => $item,
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        $data = [
            'titel' => trim($post['titel'] ?? ''),
            'omschrijving' => trim($post['omschrijving'] ?? '') ?: null,
            'type' => in_array($post['type'] ?? '', array_keys(self::SCRIPT_TYPES), true) ? $post['type'] : 'overig',
            'inhoud' => trim($post['inhoud'] ?? ''),
        ];

        if (!$isUpdate) {
            $data['auteur_id'] = $this->currentUserId();
        }

        return $data;
    }
}
