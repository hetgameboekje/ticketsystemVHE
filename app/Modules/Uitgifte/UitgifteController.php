<?php

namespace App\Modules\Uitgifte;

use App\Core\CrudController;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Uitgifte\Models\UitgifteModel;
use App\Modules\Voorraad\Models\VoorraadItemModel;

class UitgifteController extends CrudController
{
    protected string $modelClass = UitgifteModel::class;
    protected string $viewDir = 'Modules/Uitgifte/Views/UitgifteView';
    protected string $routeBase = 'uitgiften';
    protected string $activeModule = 'uitgiften';
    protected string $pageTitle = 'Uitgifte';
    protected ?string $searchColumn = 'medewerker_naam';

    protected function filterOptions(array $allItems): array
    {
        return [
            'status' => ['uitgegeven' => 'Uitgegeven', 'geretourneerd' => 'Geretourneerd'],
        ];
    }

    public function create(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $this->render("{$this->viewDir}/create", [
            'barcode' => $_GET['barcode'] ?? '',
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $barcode = trim($_POST['barcode'] ?? '');
        $medewerkerNaam = trim($_POST['medewerker_naam'] ?? '');
        $uitgegevenOp = $_POST['uitgegeven_op'] !== '' ? $_POST['uitgegeven_op'] : date('Y-m-d');
        $opmerking = trim($_POST['opmerking'] ?? '') ?: null;

        if ($barcode === '' || $medewerkerNaam === '') {
            $_SESSION['flash_error'] = 'Barcode en naam zijn verplicht.';
            $this->redirect('/uitgiften/create?barcode=' . urlencode($barcode));
        }

        $item = VoorraadItemModel::findAvailableByBarcode($barcode);
        if ($item === null) {
            $_SESSION['flash_error'] = "Geen beschikbaar voorraaditem gevonden met barcode \"{$barcode}\" (al uitgegeven of onbekend).";
            $this->redirect('/uitgiften/create?barcode=' . urlencode($barcode));
        }

        $id = UitgifteModel::create([
            'voorraad_item_id' => $item['id'],
            'medewerker_naam' => $medewerkerNaam,
            'uitgegeven_op' => $uitgegevenOp,
            'opmerking' => $opmerking,
            'uitgegeven_door_id' => $this->currentUserId(),
        ]);

        VoorraadItemModel::setStatus((int) $item['id'], 'uitgegeven');

        $_SESSION['flash_success'] = "{$item['type_naam']} ({$item['barcode']}) toegewezen aan {$medewerkerNaam}.";
        $this->redirect("/uitgiften/{$id}");
    }

    public function retour(int $id): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');
        $uitgifte = UitgifteModel::findWithRelations($id);

        if ($uitgifte === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $opmerking = trim($_POST['opmerking'] ?? '') ?: null;

        UitgifteModel::setTeruggegeven($id, date('Y-m-d'), $opmerking);
        VoorraadItemModel::setStatus((int) $uitgifte['voorraad_item_id'], 'op_voorraad');

        $_SESSION['flash_success'] = "{$uitgifte['type_naam']} ({$uitgifte['barcode']}) is retour genomen.";
        $this->redirect('/uitgiften');
    }

    public function namen(): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $q = trim($_GET['q'] ?? '');

        header('Content-Type: application/json');
        echo json_encode($q === '' ? [] : MedewerkerModel::searchNamen($q));
    }

    public function items(): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $q = trim($_GET['q'] ?? '');
        $items = VoorraadItemModel::searchAvailable($q);

        $results = array_map(static function (array $item): array {
            $naam = $item['type_naam'] ?? 'Item';
            if (!empty($item['variant'])) {
                $naam .= ' (' . $item['variant'] . ')';
            }
            return [
                'barcode' => $item['barcode'],
                'label' => $naam . ' — ' . $item['barcode'],
            ];
        }, $items);

        header('Content-Type: application/json');
        echo json_encode($results);
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        return [];
    }
}
