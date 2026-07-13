<?php

namespace App\Modules\Medewerker;

use App\Core\CrudController;
use App\Modules\Device\Models\DeviceModel;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Schijfgebruik\Models\SchijfgebruikDeviceModel;
use App\Modules\Uitgifte\Models\UitgifteModel;
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

    public function show(int $id): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $item = MedewerkerModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $naam = trim($item['voornaam'] . ' ' . $item['achternaam']);

        $hostnames = array_filter(array_map('trim', explode(',', $item['apparaat_hostnames'] ?? '')));
        $schijfgebruik = array_values(array_filter(array_map(
            fn (string $hostnaam) => SchijfgebruikDeviceModel::findByNaam($hostnaam),
            $hostnames
        )));

        $this->render("{$this->viewDir}/show", [
            'item' => $item,
            'uitgiften' => UitgifteModel::forMedewerkerNaam($naam),
            'apparaten' => DeviceModel::forMedewerker($id),
            'schijfgebruik' => $schijfgebruik,
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    /**
     * Importeert een gebruikers-export CSV: werkt medewerkers bij op e-mail of maakt ze aan, en
     * koppelt de meegeleverde apparaat-hostnamen best-effort aan bestaande apparaten (zie
     * DeviceModel::findByNaamMatch()). De hostnamen worden bewaard op de medewerker zelf, zodat
     * show() daarmee ook de bijbehorende schijfgebruik-apparaten kan opzoeken.
     */
    public function import(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        if (empty($_FILES['bestand']['tmp_name']) || $_FILES['bestand']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Geen geldig CSV-bestand ontvangen.';
            $this->redirect('/medewerkers');
        }

        $extension = strtolower(pathinfo($_FILES['bestand']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $_SESSION['flash_error'] = 'Alleen .csv-bestanden worden ondersteund.';
            $this->redirect('/medewerkers');
        }

        try {
            $rows = MedewerkerImport::parse($_FILES['bestand']['tmp_name']);
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/medewerkers');
        }

        $aangemaakt = 0;
        $bijgewerkt = 0;
        $apparatenGekoppeld = 0;

        foreach ($rows as $row) {
            $bestaand = MedewerkerModel::findByEmail($row['email']);
            $exceptId = $bestaand !== null ? (int) $bestaand['id'] : null;

            $data = [
                'voornaam' => $row['voornaam'],
                'achternaam' => $row['achternaam'],
                'email' => $row['email'],
                'telefoon' => $row['telefoon'],
                'status' => $row['status'],
                'apparaat_hostnames' => implode(', ', $row['hostnames']),
                'user_id' => $this->gekoppeldeUserId($row['email'], $exceptId),
            ];

            if ($bestaand !== null) {
                $medewerkerId = (int) $bestaand['id'];
                MedewerkerModel::update($medewerkerId, $data);
                $bijgewerkt++;
            } else {
                $medewerkerId = MedewerkerModel::create($data);
                $aangemaakt++;
            }

            foreach ($row['hostnames'] as $hostnaam) {
                $device = DeviceModel::findByNaamMatch($hostnaam);
                if ($device !== null) {
                    DeviceModel::update((int) $device['id'], ['medewerker_id' => $medewerkerId]);
                    $apparatenGekoppeld++;
                }
            }
        }

        $_SESSION['flash_success'] = "Import voltooid: {$aangemaakt} medewerker(s) aangemaakt, {$bijgewerkt} bijgewerkt, "
            . "{$apparatenGekoppeld} apparaat/apparaten gekoppeld.";
        $this->redirect('/medewerkers');
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
