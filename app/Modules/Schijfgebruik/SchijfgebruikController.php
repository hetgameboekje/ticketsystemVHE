<?php

namespace App\Modules\Schijfgebruik;

use App\Core\Controller;
use App\Core\TableQuery;
use App\Modules\Schijfgebruik\Models\SchijfgebruikDeviceModel;
use App\Modules\Schijfgebruik\Models\SchijfgebruikSchijfModel;

class SchijfgebruikController extends Controller
{
    private const ACTIVE_MODULE = 'schijfgebruik';

    public function index(): void
    {
        $this->requirePermission(self::ACTIVE_MODULE, 'lezen');

        $allItems = SchijfgebruikSchijfModel::allWithDevice();

        $minGebruik = trim((string) ($_GET['min_gebruik'] ?? ''));
        if ($minGebruik !== '' && is_numeric($minGebruik)) {
            $drempel = (int) $minGebruik;
            $allItems = array_values(array_filter(
                $allItems,
                fn (array $row) => (int) $row['gebruik_percentage'] >= $drempel
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

        $params = array_diff_key($_GET, ['min_gebruik' => null, 'q' => null]);
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
            'activeModule' => self::ACTIVE_MODULE,
            'pageTitle' => 'Schijfgebruik',
            'sort' => $params['sort'],
            'dir' => $params['dir'],
        ]);
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
