<?php

namespace App\Modules\Voorraad;

use App\Core\Barcode;
use App\Core\CrudController;
use App\Modules\Voorraad\Models\VoorraadItemModel;
use App\Modules\Voorraad\Models\VoorraadTypeModel;

class VoorraadController extends CrudController
{
    protected string $modelClass = VoorraadItemModel::class;
    protected string $viewDir = 'Modules/Voorraad/Views/VoorraadView';
    protected string $routeBase = 'voorraad';
    protected string $activeModule = 'voorraad';
    protected string $pageTitle = 'Voorraad';
    protected ?string $searchColumn = 'barcode';

    private const STATUS_LABELS = [
        'op_voorraad' => 'Op voorraad',
        'uitgegeven' => 'Uitgegeven',
    ];

    protected function filterOptions(array $allItems): array
    {
        $types = VoorraadTypeModel::all();
        $typeOptions = [];
        foreach ($types as $t) {
            $typeOptions[$t['naam']] = $t['naam'];
        }

        return [
            'type_naam' => $typeOptions,
            'status' => self::STATUS_LABELS,
        ];
    }

    public function store(): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');

        $typeId = (int) ($_POST['type_id'] ?? 0);
        $type = VoorraadTypeModel::find($typeId);

        if ($type === null) {
            $_SESSION['flash_error'] = 'Kies een geldig type.';
            $this->redirect('/voorraad/create');
        }

        $variant = trim($_POST['variant'] ?? '') ?: null;
        $serienummer = trim($_POST['serienummer'] ?? '') ?: null;
        $locatie = trim($_POST['locatie'] ?? '') ?: null;
        $opmerking = trim($_POST['opmerking'] ?? '') ?: null;
        $aantal = max(1, (int) ($_POST['aantal'] ?? 1));

        if ($serienummer !== null && $aantal > 1) {
            $_SESSION['flash_error'] = 'Met een serienummer kan je maar 1 item tegelijk toevoegen (een serienummer is uniek per stuk).';
            $this->redirect('/voorraad/create');
        }

        $barcode = self::buildBarcode($type['code'], $variant, $serienummer);

        $lastId = null;
        for ($i = 0; $i < $aantal; $i++) {
            $lastId = VoorraadItemModel::create([
                'type_id' => $typeId,
                'variant' => $variant,
                'serienummer' => $serienummer,
                'barcode' => $barcode,
                'locatie' => $locatie,
                'opmerking' => $opmerking,
                'aangemaakt_door_id' => $this->currentUserId(),
            ]);
        }

        $_SESSION['flash_success'] = $aantal > 1
            ? "{$aantal} items toegevoegd met barcode {$barcode}."
            : "Item toegevoegd met barcode {$barcode}.";
        $this->redirect("/voorraad/{$lastId}");
    }

    public function update(int $id): void
    {
        $this->requirePermission($this->activeModule, 'schrijven');
        $item = VoorraadItemModel::find($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $typeId = (int) ($_POST['type_id'] ?? 0);
        $type = VoorraadTypeModel::find($typeId);

        if ($type === null) {
            $_SESSION['flash_error'] = 'Kies een geldig type.';
            $this->redirect("/voorraad/{$id}/edit");
        }

        $variant = trim($_POST['variant'] ?? '') ?: null;
        $serienummer = trim($_POST['serienummer'] ?? '') ?: null;
        $locatie = trim($_POST['locatie'] ?? '') ?: null;
        $opmerking = trim($_POST['opmerking'] ?? '') ?: null;

        $barcode = self::buildBarcode($type['code'], $variant, $serienummer);

        VoorraadItemModel::update($id, [
            'type_id' => $typeId,
            'variant' => $variant,
            'serienummer' => $serienummer,
            'barcode' => $barcode,
            'locatie' => $locatie,
            'opmerking' => $opmerking,
        ]);

        $_SESSION['flash_success'] = "Item bijgewerkt (barcode: {$barcode}).";
        $this->redirect("/voorraad/{$id}");
    }

    public function show(int $id): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $item = VoorraadItemModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render("{$this->viewDir}/show", [
            'item' => $item,
            'barcodeSvg' => Barcode::code128Svg($item['barcode']),
            'activeModule' => $this->activeModule,
            'pageTitle' => $this->pageTitle,
            'routeBase' => $this->routeBase,
        ]);
    }

    public function barcode(int $id): void
    {
        $this->requirePermission($this->activeModule, 'lezen');
        $item = VoorraadItemModel::findWithRelations($id);

        if ($item === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $svg = Barcode::code128Svg($item['barcode'], 3, 70);
        $naam = htmlspecialchars($item['type_naam'] . ($item['variant'] ? ' (' . $item['variant'] . ')' : ''));

        echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><title>Barcode ' . htmlspecialchars($item['barcode']) . '</title>'
            . '<style>body{font-family:sans-serif;text-align:center;padding:24px}.label{display:inline-block;border:1px dashed #ccc;padding:12px 16px;margin:8px}
               .naam{font-size:13px;margin-bottom:6px}
               @media print{.no-print{display:none}}</style></head><body>'
            . '<div class="no-print"><button onclick="window.print()">Printen</button></div>'
            . '<div class="label"><div class="naam">' . $naam . '</div>' . $svg . '</div>'
            . '</body></html>';
    }

    protected function formData(): array
    {
        return ['types' => VoorraadTypeModel::all()];
    }

    protected function validatedData(array $post, bool $isUpdate = false): array
    {
        return [];
    }

    private static function buildBarcode(string $typeCode, ?string $variant, ?string $serienummer): string
    {
        if ($serienummer !== null && $serienummer !== '') {
            return strtoupper($typeCode . '-' . preg_replace('/[^A-Za-z0-9]/', '', $serienummer));
        }

        $suffix = ($variant !== null && $variant !== '')
            ? '-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $variant))
            : '';

        return strtoupper($typeCode) . $suffix;
    }
}
