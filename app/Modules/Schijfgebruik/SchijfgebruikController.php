<?php

namespace App\Modules\Schijfgebruik;

use App\Core\Controller;
use App\Core\TableQuery;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Schijfgebruik\Models\SchijfgebruikDeviceModel;
use App\Modules\Schijfgebruik\Models\SchijfgebruikSchijfModel;

class SchijfgebruikController extends Controller
{
    private const ACTIVE_MODULE = 'schijfgebruik';

    public function index(): void
    {
        $this->requirePermission(self::ACTIVE_MODULE, 'lezen');

        $allItems = array_map(
            fn (array $row) => array_merge($row, SchijfgebruikHealth::evaluate($row)),
            SchijfgebruikSchijfModel::allWithDevice()
        );

        $minGebruik = trim((string) ($_GET['min_gebruik'] ?? ''));
        if ($minGebruik !== '' && is_numeric($minGebruik)) {
            $drempel = (int) $minGebruik;
            $allItems = array_values(array_filter(
                $allItems,
                fn (array $row) => (int) $row['gebruik_percentage'] >= $drempel
            ));
        }

        $alleenWaarschuwingen = ($_GET['alleen_waarschuwingen'] ?? '') === '1';
        if ($alleenWaarschuwingen) {
            $allItems = array_values(array_filter(
                $allItems,
                fn (array $row) => !empty($row['waarschuwingen'])
            ));
        }

        // Zoekt op zowel apparaatnaam als gebruiker — TableQuery::apply() ondersteunt maar één
        // zoekkolom, dus dit gebeurt hier zelf en 'q' wordt hieronder uit de filterparams gehaald.
        $search = trim((string) ($_GET['q'] ?? ''));
        if ($search !== '') {
            $allItems = array_values(array_filter(
                $allItems,
                fn (array $row) => stripos($row['naam'], $search) !== false
                    || stripos((string) $row['laatste_login'], $search) !== false
            ));
        }

        $filterOptions = $this->filterOptions($allItems);

        $params = array_diff_key($_GET, ['min_gebruik' => null, 'q' => null, 'alleen_waarschuwingen' => null]);
        $params['sort'] = $params['sort'] ?? 'gebruik_percentage';
        $params['dir'] = $params['dir'] ?? 'desc';

        $items = TableQuery::apply($allItems, $params);
        $pagination = TableQuery::paginate($items, $_GET);

        $this->render('Modules/Schijfgebruik/Views/SchijfgebruikView/index', [
            'items' => $pagination['items'],
            'pagination' => $pagination,
            'filterOptions' => $filterOptions,
            'search' => $search,
            'minGebruik' => $minGebruik,
            'alleenWaarschuwingen' => $alleenWaarschuwingen,
            'activeModule' => self::ACTIVE_MODULE,
            'pageTitle' => 'Schijfgebruik',
            'sort' => $params['sort'],
            'dir' => $params['dir'],
        ]);
    }

    public function show(int $id): void
    {
        $this->requirePermission(self::ACTIVE_MODULE, 'lezen');

        $device = SchijfgebruikDeviceModel::findWithSchijven($id);
        if ($device === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $maxGebruik = $device['schijven'] === [] ? 0 : max(array_column($device['schijven'], 'gebruik_percentage'));
        $health = SchijfgebruikHealth::evaluate(array_merge($device, ['gebruik_percentage' => $maxGebruik]));

        $this->render('Modules/Schijfgebruik/Views/SchijfgebruikView/show', [
            'device' => $device,
            'health' => $health,
            'medewerkers' => MedewerkerModel::all('achternaam ASC'),
            'activeModule' => self::ACTIVE_MODULE,
            'pageTitle' => 'Schijfgebruik',
        ]);
    }

    public function koppelMedewerker(int $id): void
    {
        $this->requirePermission(self::ACTIVE_MODULE, 'schrijven');

        $device = SchijfgebruikDeviceModel::find($id);
        if ($device === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $medewerkerId = ($_POST['medewerker_id'] ?? '') !== '' ? (int) $_POST['medewerker_id'] : null;
        SchijfgebruikDeviceModel::setMedewerker($id, $medewerkerId);

        $_SESSION['flash_success'] = 'Medewerker gekoppeld.';
        $this->redirect("/schijfgebruik/{$id}");
    }

    public function upload(): void
    {
        $this->requirePermission(self::ACTIVE_MODULE, 'schrijven');

        if (empty($_FILES['bestand']['tmp_name']) || $_FILES['bestand']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Geen geldig CSV-bestand ontvangen.';
            $this->redirect('/schijfgebruik');
        }

        $extension = strtolower(pathinfo($_FILES['bestand']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $_SESSION['flash_error'] = 'Alleen .csv-bestanden worden ondersteund.';
            $this->redirect('/schijfgebruik');
        }

        try {
            $parsed = SchijfgebruikImport::parse($_FILES['bestand']['tmp_name']);
            $result = SchijfgebruikDeviceModel::replaceAll($parsed);
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/schijfgebruik');
        }

        $_SESSION['flash_success'] = "Import voltooid: {$result['apparaten']} apparaat/apparaten, {$result['schijven']} schijf/schijven.";
        $this->redirect('/schijfgebruik');
    }

    private function filterOptions(array $items): array
    {
        $build = function (string $key) use ($items): array {
            $values = array_values(array_unique(array_filter(array_column($items, $key))));
            sort($values);
            return array_combine($values, $values);
        };

        return [
            'organisatie' => $build('organisatie'),
            'locatie' => $build('locatie'),
            'letter' => $build('letter'),
        ];
    }
}
