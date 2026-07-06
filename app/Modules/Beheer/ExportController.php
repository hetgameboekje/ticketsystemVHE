<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Core\Xlsx;
use App\Modules\Agenda\Models\AgendaItemModel;
use App\Modules\CyberRisico\Models\CyberRisicoModel;
use App\Modules\HardwareUitgave\Models\HardwareUitgaveModel;
use App\Modules\Kennisbank\Models\KennisbankModel;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Printer\Models\PrinterModel;
use App\Modules\Reflectie\Models\ReflectieModel;
use App\Modules\Ticket\Models\TicketModel;
use App\Modules\Uitgifte\Models\UitgifteModel;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;
use App\Modules\Voorraad\Models\VoorraadItemModel;
use App\Shared\User\Models\UserModel;

class ExportController extends Controller
{
    /** module-key => [label, model class]. Wordt zowel voor de keuzelijst als de export zelf gebruikt. */
    private const EXPORTEERBAAR = [
        'tickets' => ['Tickets', TicketModel::class],
        'verbeterpunten' => ['Verbeterpunten', VerbeterpuntModel::class],
        'reflecties' => ['Reflectie', ReflectieModel::class],
        'kennisbank' => ['Kennisbank', KennisbankModel::class],
        'cyberrisicos' => ["Cyberrisico's", CyberRisicoModel::class],
        'voorraad' => ['Voorraad', VoorraadItemModel::class],
        'uitgiften' => ['Uitgifte', UitgifteModel::class],
        'medewerkers' => ['Medewerkers', MedewerkerModel::class],
        'hardware_uitgaven' => ['Hardware-uitgaven', HardwareUitgaveModel::class],
        'printers' => ['Printers', PrinterModel::class],
        'agenda' => ['Agenda', AgendaItemModel::class],
        'gebruikers' => ['Gebruikers', UserModel::class],
    ];

    /** Kolommen die nooit meegeëxporteerd worden, ongeacht welke module. */
    private const GEVOELIGE_KOLOMMEN = ['wachtwoord_hash'];

    public function index(): void
    {
        $this->requireAdmin();

        $labels = array_map(fn (array $m) => $m[0], self::EXPORTEERBAAR);

        $this->render('Modules/Beheer/Views/ExportView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Exporteren',
            'modules' => $labels,
        ]);
    }

    public function export(): void
    {
        $this->requireAdmin();

        $gekozen = array_intersect($_POST['modules'] ?? [], array_keys(self::EXPORTEERBAAR));
        $formaat = ($_POST['formaat'] ?? 'excel') === 'csv' ? 'csv' : 'excel';

        if (empty($gekozen)) {
            $_SESSION['flash_error'] = 'Kies minstens één module om te exporteren.';
            $this->redirect('/beheer/exporteren');
        }

        $datasets = [];
        foreach ($gekozen as $key) {
            [$label, $modelClass] = self::EXPORTEERBAAR[$key];
            $rows = method_exists($modelClass, 'allWithRelations') ? $modelClass::allWithRelations() : $modelClass::all();
            $datasets[$key] = ['label' => $label, 'rows' => $this->stripGevoeligeKolommen($rows)];
        }

        if ($formaat === 'excel') {
            $this->exportExcel($datasets);
        } else {
            $this->exportCsv($datasets);
        }
    }

    private function stripGevoeligeKolommen(array $rows): array
    {
        foreach ($rows as &$row) {
            foreach (self::GEVOELIGE_KOLOMMEN as $kolom) {
                unset($row[$kolom]);
            }
        }

        return $rows;
    }

    private function exportExcel(array $datasets): void
    {
        $sheets = [];
        foreach ($datasets as $dataset) {
            $headers = empty($dataset['rows']) ? [] : array_keys($dataset['rows'][0]);
            $rows = array_map(fn (array $row) => array_values($row), $dataset['rows']);
            $sheets[] = ['name' => substr($dataset['label'], 0, 31), 'headers' => $headers, 'rows' => $rows];
        }

        $content = Xlsx::writeMultiSheet($sheets, $this->currentUser()['naam'] ?? 'Ticketsysteem VHE');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export-' . date('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    private function exportCsv(array $datasets): void
    {
        if (count($datasets) === 1) {
            $dataset = reset($datasets);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . key($datasets) . '-' . date('Y-m-d') . '.csv"');
            echo $this->csvContent($dataset['rows']);
            return;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'export_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);
        foreach ($datasets as $key => $dataset) {
            $zip->addFromString($key . '.csv', $this->csvContent($dataset['rows']));
        }
        $zip->close();

        $content = file_get_contents($tmp);
        unlink($tmp);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="export-' . date('Y-m-d') . '.zip"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    private function csvContent(array $rows): string
    {
        $fp = fopen('php://temp', 'r+');

        // BOM zodat Excel de UTF-8-inhoud correct herkent i.p.v. als Windows-1252 te lezen.
        fwrite($fp, "\xEF\xBB\xBF");

        if (!empty($rows)) {
            fputcsv($fp, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($fp, $row, ';');
            }
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);

        return $content;
    }
}
