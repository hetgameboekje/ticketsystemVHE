<?php

namespace App\Modules\Tools;

use App\Core\Controller;
use App\Core\Mailer;
use App\Modules\Schijfgebruik\Models\SchijfgebruikDeviceModel;
use App\Modules\Schijfgebruik\SchijfgebruikHealth;
use App\Modules\Tools\Models\RestartHerinneringInstellingenModel;
use Throwable;

class RestartReminderController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/RestartReminderView/index', [
            'activeModule' => 'tools',
            'pageTitle' => 'Herstart-herinneringen',
            'apparaten' => $this->apparatenDieHerstartNodigHebben(),
            'instellingen' => RestartHerinneringInstellingenModel::ophalen(),
        ]);
    }

    public function instellingenOpslaan(): void
    {
        $this->requireAuth();

        RestartHerinneringInstellingenModel::opslaan(
            trim((string) ($_POST['onderwerp'] ?? '')) ?: 'Even je apparaat herstarten?',
            (string) ($_POST['inhoud'] ?? ''),
            trim((string) ($_POST['cc'] ?? '')) ?: null,
            trim((string) ($_POST['bcc'] ?? '')) ?: null
        );

        $_SESSION['flash_success'] = 'Instellingen opgeslagen.';
        $this->redirect('/tools/herstart-herinneringen');
    }

    public function exportCsv(): void
    {
        $this->requireAuth();

        $rows = array_map(static function (array $a): array {
            return [
                'medewerker' => $a['medewerker_naam'] ?? '—',
                'email' => $a['email'] ?? '—',
                'apparaat' => $a['naam'],
                'dagen_sinds_boot' => $a['dagen_sinds_boot'],
            ];
        }, $this->apparatenDieHerstartNodigHebben());

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, ['medewerker', 'email', 'apparaat', 'dagen_sinds_boot'], ';');
        foreach ($rows as $row) {
            fputcsv($fp, $row, ';');
        }
        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="herstart-herinneringen-' . date('Y-m-d') . '.csv"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    public function versturen(): void
    {
        $this->requireAuth();

        $instellingen = RestartHerinneringInstellingenModel::ophalen();
        $cc = RestartHerinneringInstellingenModel::adressenUitVeld($instellingen['cc']);
        $bcc = RestartHerinneringInstellingenModel::adressenUitVeld($instellingen['bcc']);

        $verstuurd = 0;
        $overgeslagen = 0;

        foreach ($this->apparatenDieHerstartNodigHebben() as $a) {
            if (empty($a['email'])) {
                $overgeslagen++;
                continue;
            }

            $inhoud = str_replace(
                ['{naam}', '{apparaat}', '{dagen}'],
                [$a['medewerker_naam'] ?? $a['naam'], $a['naam'], (string) $a['dagen_sinds_boot']],
                $instellingen['inhoud']
            );

            try {
                Mailer::verstuur($a['email'], $instellingen['onderwerp'], $inhoud, $cc, $bcc);
                $verstuurd++;
            } catch (Throwable $e) {
                $overgeslagen++;
            }
        }

        $_SESSION['flash_success'] = "{$verstuurd} herinnering(en) verstuurd, {$overgeslagen} overgeslagen (geen e-mailadres of verzendfout).";
        $this->redirect('/tools/herstart-herinneringen');
    }

    /** @return array<int, array<string, mixed>> apparaten die herstart nodig hebben, met medewerker-naam/e-mail en dagen sinds boot. */
    private function apparatenDieHerstartNodigHebben(): array
    {
        $resultaat = [];

        foreach (SchijfgebruikDeviceModel::alleMetMedewerker() as $a) {
            $health = SchijfgebruikHealth::evaluate([
                'laatst_online' => $a['laatst_online'],
                'laatste_boot' => $a['laatste_boot'],
                'gebruik_percentage' => 0,
            ]);

            if (!$health['herstart_nodig']) {
                continue;
            }

            $a['medewerker_naam'] = $a['medewerker_id'] !== null ? trim($a['voornaam'] . ' ' . $a['achternaam']) : null;
            $a['dagen_sinds_boot'] = $health['dagen_sinds_boot'];
            $resultaat[] = $a;
        }

        return $resultaat;
    }
}
