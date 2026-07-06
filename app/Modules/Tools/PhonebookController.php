<?php

namespace App\Modules\Tools;

use App\Core\Controller;
use App\Core\Xlsx;
use App\Modules\Tools\Lib\VCardGenerator;
use App\Modules\Tools\Models\PhonebookJobModel;
use Throwable;

class PhonebookController extends Controller
{
    private string $uploadDir;
    private string $outputDir;

    public function __construct()
    {
        $this->uploadDir = APP_ROOT . '/public/uploads/tools/telefoonlijsten';
        $this->outputDir = APP_ROOT . '/public/uploads/tools/vcf';
    }

    public function index(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/PhonebookView/index', [
            'activeModule' => 'tools',
            'pageTitle' => 'Telefoonlijst naar VCF',
            'jobs' => PhonebookJobModel::all(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $file = $_FILES['bestand'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Uploaden is mislukt, probeer het opnieuw.';
            $this->redirect('/tools/telefoonlijst');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') {
            $_SESSION['flash_error'] = 'Alleen .xlsx-bestanden worden ondersteund.';
            $this->redirect('/tools/telefoonlijst');
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $storedName = uniqid('telefoonlijst_', true) . '.xlsx';
        $storedPath = $this->uploadDir . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
            $_SESSION['flash_error'] = 'Het bestand kon niet worden opgeslagen.';
            $this->redirect('/tools/telefoonlijst');
        }

        $id = PhonebookJobModel::create([
            'original_filename' => $file['name'],
            'stored_path' => $storedPath,
            'status' => 'queued',
        ]);

        $this->redirect("/tools/telefoonlijst/{$id}");
    }

    public function show(int $id): void
    {
        $this->requireAuth();

        $job = PhonebookJobModel::find($id);
        if ($job === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        if ($job['status'] === 'queued') {
            $this->processJob($job);
            $job = PhonebookJobModel::find($id);
        }

        $this->render('Modules/Tools/Views/PhonebookView/show', [
            'activeModule' => 'tools',
            'pageTitle' => 'Telefoonlijst verwerken',
            'job' => $job,
        ]);
    }

    public function download(int $id): void
    {
        $this->requireAuth();

        $job = PhonebookJobModel::find($id);
        if ($job === null || $job['status'] !== 'done' || !$job['result_path'] || !is_file($job['result_path'])) {
            http_response_code(404);
            echo 'Bestand niet gevonden.';
            return;
        }

        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="telefoonlijst.vcf"');
        header('Content-Length: ' . filesize($job['result_path']));
        readfile($job['result_path']);
    }

    public function destroy(int $id): void
    {
        $this->requireAuth();

        $job = PhonebookJobModel::find($id);
        if ($job !== null) {
            if (is_file($job['stored_path'])) {
                unlink($job['stored_path']);
            }
            if ($job['result_path'] && is_file($job['result_path'])) {
                unlink($job['result_path']);
            }
            PhonebookJobModel::delete($id);
        }

        $this->redirect('/tools/telefoonlijst');
    }

    private function processJob(array $job): void
    {
        PhonebookJobModel::markProcessing((int) $job['id']);

        try {
            $sheetNames = Xlsx::sheetNames($job['stored_path']);
            if (empty($sheetNames)) {
                throw new \RuntimeException('Geen werkblad gevonden in het Excel-bestand.');
            }

            $sheet = Xlsx::readSheet($job['stored_path'], $sheetNames[0]);
            $rows = $this->sheetToAssocRows($sheet);
            $vcf = VCardGenerator::build($rows);

            if (!is_dir($this->outputDir)) {
                mkdir($this->outputDir, 0777, true);
            }

            $resultPath = $this->outputDir . '/telefoonlijst_' . $job['id'] . '.vcf';
            file_put_contents($resultPath, $vcf);

            PhonebookJobModel::markDone((int) $job['id'], $resultPath, VCardGenerator::countCards($vcf));
        } catch (Throwable $e) {
            PhonebookJobModel::markError((int) $job['id'], $e->getMessage());
        }
    }

    /**
     * Zet de kolom-geïndexeerde output van Xlsx::readSheet() om naar rijen die op
     * kleine-letter-kopnaam zijn geïndexeerd (wat VCardGenerator verwacht), en slaat
     * volledig lege rijen over.
     * @return array<int, array<string, string>>
     */
    private function sheetToAssocRows(array $sheet): array
    {
        $headers = array_map(fn (string $h) => strtolower(trim($h)), $sheet['headers']);

        $rows = [];
        foreach ($sheet['rows'] as $rawRow) {
            $row = [];
            foreach ($headers as $i => $header) {
                if ($header !== '') {
                    $row[$header] = trim((string) ($rawRow[$i] ?? ''));
                }
            }

            if (implode('', $row) !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
