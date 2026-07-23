<?php

namespace App\Modules\Tools;

use App\Core\Controller;
use App\Modules\Device\Models\DeviceModel;
use App\Modules\Tools\Models\InstallatieApplicatieModel;
use App\Modules\Tools\Models\InstallatieOpdrachtModel;
use App\Modules\Tools\Models\InstallatieProfielItemModel;
use App\Modules\Tools\Models\InstallatieProfielModel;
use App\Modules\Voorraad\Models\VoorraadItemModel;

class InstallatieController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/InstallatieView/index', [
            'activeModule' => 'tools',
            'pageTitle' => 'Installatie',
            'applicaties' => InstallatieApplicatieModel::alle(),
            'profielen' => InstallatieProfielModel::alleMetItems(),
            'opdrachten' => InstallatieOpdrachtModel::allWithRelations(),
        ]);
    }

    public function applicatieStore(): void
    {
        $this->requireAuth();

        $naam = trim($_POST['naam'] ?? '');
        if ($naam !== '') {
            InstallatieApplicatieModel::toevoegen($naam);
        }

        $this->redirect('/tools/installatie');
    }

    public function applicatieDestroy(int $id): void
    {
        $this->requireAuth();

        InstallatieApplicatieModel::delete($id);
        $this->redirect('/tools/installatie');
    }

    public function profielStore(): void
    {
        $this->requireAuth();

        $naam = trim($_POST['naam'] ?? '');
        if ($naam !== '') {
            InstallatieProfielModel::create(['naam' => $naam]);
        }

        $this->redirect('/tools/installatie');
    }

    public function profielDestroy(int $id): void
    {
        $this->requireAuth();

        InstallatieProfielModel::delete($id);
        $this->redirect('/tools/installatie');
    }

    public function profielItemStore(int $profielId): void
    {
        $this->requireAuth();

        $naam = trim($_POST['naam'] ?? '');
        if ($naam !== '') {
            InstallatieProfielItemModel::toevoegen($profielId, $naam);
        }

        $this->redirect('/tools/installatie');
    }

    public function profielItemDestroy(int $profielId, int $itemId): void
    {
        $this->requireAuth();

        InstallatieProfielItemModel::delete($itemId);
        $this->redirect('/tools/installatie');
    }

    /** JSON-zoeksuggesties voor het apparaat-zoekveld bij het aanmaken van een opdracht. */
    public function apparatenZoeken(): void
    {
        $this->requireAuth();

        $q = trim($_GET['q'] ?? '');

        header('Content-Type: application/json');
        echo json_encode($q === '' ? [] : DeviceModel::zoekNamen($q));
    }

    public function opdrachtCreate(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/InstallatieView/opdracht_create', [
            'activeModule' => 'tools',
            'pageTitle' => 'Installatie',
            'profielen' => InstallatieProfielModel::alleMetItems(),
        ]);
    }

    /**
     * Koppelt de opdracht aan een apparaat via naam: bestaat het al (exacte of gedeeltelijke match,
     * zie DeviceModel::findByNaamMatch()), dan wordt dat hergebruikt; anders wordt er direct een
     * nieuw apparaat aangemaakt — zelfde auto-aanmaak-patroon als onbekende voorraad-barcodes in
     * UitgifteController::store(). Staat het apparaat nog niet in Voorraad, dan wordt daar ook
     * automatisch een item voor aangemaakt (VoorraadItemModel::createVoorApparaat()).
     */
    public function opdrachtStore(): void
    {
        $this->requireAuth();

        $apparaatNaam = trim($_POST['apparaat_naam'] ?? '');
        if ($apparaatNaam === '') {
            $_SESSION['flash_error'] = 'Vul een apparaatnaam in.';
            $this->redirect('/tools/installatie/opdrachten/nieuw');
        }

        $device = DeviceModel::findByNaamMatch($apparaatNaam);
        $deviceId = $device !== null ? (int) $device['id'] : DeviceModel::create(['naam' => $apparaatNaam]);

        if (VoorraadItemModel::findByDeviceId($deviceId) === null) {
            VoorraadItemModel::createVoorApparaat($deviceId, $apparaatNaam, $this->currentUserId());
        }

        $profielIds = array_map('intval', $_POST['profielen'] ?? []);
        $opmerking = trim($_POST['opmerking'] ?? '') ?: null;

        $opdrachtId = InstallatieOpdrachtModel::aanmaken($deviceId, $profielIds, $opmerking, $this->currentUserId());

        $_SESSION['flash_success'] = $device === null
            ? "Checklist aangemaakt — \"{$apparaatNaam}\" stond nog niet in Apparaten en is automatisch toegevoegd (ook in Voorraad)."
            : "Checklist toegewezen aan {$device['naam']}.";
        $this->redirect("/tools/installatie/opdrachten/{$opdrachtId}");
    }

    public function opdrachtShow(int $id): void
    {
        $this->requireAuth();
        $opdracht = InstallatieOpdrachtModel::findWithRelations($id);

        if ($opdracht === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $this->render('Modules/Tools/Views/InstallatieView/opdracht_show', [
            'activeModule' => 'tools',
            'pageTitle' => 'Installatie',
            'opdracht' => $opdracht,
            'items' => InstallatieOpdrachtModel::items($id),
            'profielNamen' => InstallatieOpdrachtModel::profielNamen($id),
        ]);
    }

    public function opdrachtItemToggle(int $opdrachtId, int $itemId): void
    {
        $this->requireAuth();

        InstallatieOpdrachtModel::toggleItem($opdrachtId, $itemId);
        $this->redirect("/tools/installatie/opdrachten/{$opdrachtId}");
    }

    public function opdrachtDestroy(int $id): void
    {
        $this->requireAuth();

        InstallatieOpdrachtModel::delete($id);
        $_SESSION['flash_success'] = 'Checklist verwijderd.';
        $this->redirect('/tools/installatie');
    }

    /** Blanco printbare checklist — hoofdlijst + eventueel gekozen profielen, geen opdracht nodig. */
    public function print(): void
    {
        $this->requireAuth();

        $profielIds = array_map('intval', $_GET['profielen'] ?? []);
        $items = array_map(
            fn (array $app) => ['naam' => $app['naam'], 'afgevinkt' => false],
            InstallatieApplicatieModel::alle()
        );

        foreach (InstallatieProfielModel::alleMetItems() as $profiel) {
            if (in_array((int) $profiel['id'], $profielIds, true)) {
                foreach ($profiel['items'] as $item) {
                    $items[] = ['naam' => $item['naam'], 'afgevinkt' => false];
                }
            }
        }

        $this->echoPrintPage(null, $items);
    }

    /** Print-variant van een bestaande opdracht, met apparaatnaam en actuele afvink-status. */
    public function opdrachtPrint(int $id): void
    {
        $this->requireAuth();
        $opdracht = InstallatieOpdrachtModel::findWithRelations($id);

        if ($opdracht === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $items = array_map(
            fn (array $i) => ['naam' => $i['naam'], 'afgevinkt' => (bool) $i['afgevinkt']],
            InstallatieOpdrachtModel::items($id)
        );

        $this->echoPrintPage($opdracht['apparaat_naam'], $items);
    }

    /**
     * Kale, printbare pagina buiten de normale site-layout om — zelfde aanpak als
     * VoorraadController::barcode(): geen navigatie, wel een "Printen"-knop en @media print-CSS.
     *
     * @param array<int, array{naam: string, afgevinkt: bool}> $items
     */
    private function echoPrintPage(?string $apparaatNaam, array $items): void
    {
        $titel = 'Installatie-checklist';
        $rijen = '';
        foreach ($items as $item) {
            $vinkje = $item['afgevinkt'] ? '&#9745;' : '&#9744;';
            $rijen .= '<div class="regel"><span class="vinkje">' . $vinkje . '</span>' . htmlspecialchars($item['naam']) . '</div>';
        }

        $apparaatVeld = $apparaatNaam !== null
            ? '<div class="apparaat"><strong>Apparaat:</strong> ' . htmlspecialchars($apparaatNaam) . '</div>'
            : '<div class="apparaat"><strong>Apparaat:</strong> ______________________________</div>';

        echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><title>' . htmlspecialchars($titel) . '</title>'
            . '<style>
                body{font-family:sans-serif;padding:24px;max-width:700px;margin:0 auto}
                h1{font-size:18px;margin-bottom:4px}
                .apparaat{font-size:14px;margin-bottom:16px}
                .regel{font-size:14px;padding:6px 0;border-bottom:1px solid #ddd;display:flex;align-items:center;gap:8px}
                .vinkje{font-size:18px}
                @media print{.no-print{display:none}}
              </style></head><body>'
            . '<div class="no-print" style="margin-bottom:16px"><button onclick="window.print()">Printen</button></div>'
            . '<h1>' . htmlspecialchars($titel) . '</h1>'
            . $apparaatVeld
            . '<div class="lijst">' . $rijen . '</div>'
            . '</body></html>';
    }
}
