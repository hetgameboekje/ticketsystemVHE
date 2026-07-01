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
    protected ?string $searchColumn = 'achternaam';

    protected function formData(): array
    {
        return ['afdelingen' => AfdelingModel::all()];
    }

    public function edit(int $id): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $item = MedewerkerModel::find($id);
        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/edit", [
            'item' => $item,
            'afdelingen' => AfdelingModel::all(),
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $data = $this->validatedData($_POST);
        $data['user_id'] = $this->gekoppeldeUserId(trim($_POST['email'] ?? ''), null);

        $id = MedewerkerModel::create($data);
        $this->redirect("/medewerkers/{$id}");
    }

    public function update(int $id): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $data = $this->validatedData($_POST, isUpdate: true);
        $data['user_id'] = $this->gekoppeldeUserId(trim($_POST['email'] ?? ''), $id);

        MedewerkerModel::update($id, $data);
        $this->redirect("/medewerkers/{$id}");
    }

    /** AJAX: checkt of het ingevoerde e-mailadres bij een bestaande, nog niet gekoppelde login hoort. */
    public function loginCheck(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $email = trim($_GET['email'] ?? '');
        $exceptId = isset($_GET['medewerker_id']) && $_GET['medewerker_id'] !== '' ? (int) $_GET['medewerker_id'] : null;

        $status = $email !== '' ? MedewerkerModel::loginStatusVoorEmail($email, $exceptId) : 'niet_gevonden';

        header('Content-Type: application/json');
        echo json_encode(['status' => $status]);
    }

    private function gekoppeldeUserId(string $email, ?int $exceptMedewerkerId): ?int
    {
        if ($email === '' || MedewerkerModel::loginStatusVoorEmail($email, $exceptMedewerkerId) !== 'gevonden') {
            return null;
        }

        return MedewerkerModel::userIdVoorEmail($email);
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
