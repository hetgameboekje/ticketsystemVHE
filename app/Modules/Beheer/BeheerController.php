<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Core\DevSync;
use App\Core\SchemaParser;

class BeheerController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $output = $_SESSION['beheer_output'] ?? null;
        unset($_SESSION['beheer_output']);

        $this->render('Modules/Beheer/Views/BeheerView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Beheer',
            'output' => $output,
            'gitBeschikbaar' => is_dir(APP_ROOT . '/.git'),
            'devModus' => DevSync::isEnabled(),
            'gitPullEnabled' => DevSync::isGitPullEnabled(),
        ]);
    }

    public function gitPull(): void
    {
        $this->requireAdmin();

        if (!DevSync::isGitPullEnabled()) {
            $_SESSION['flash_error'] = 'Git pull staat uit voor deze server (gitPullEnabled = false — '
                . 'bedoeld voor hosts zonder shell-toegang, zoals Hostnet shared webhosting).';
            $this->redirect('/beheer');
        }

        $huidigeMap = getcwd();
        chdir(APP_ROOT);
        $output = [];
        $exitCode = 0;
        exec('git pull 2>&1', $output, $exitCode);
        chdir($huidigeMap);

        $_SESSION['beheer_output'] = "$ git pull\n" . implode("\n", $output);
        $_SESSION[$exitCode === 0 ? 'flash_success' : 'flash_error'] = $exitCode === 0
            ? 'Git pull uitgevoerd.'
            : 'Git pull gaf een foutmelding — zie de uitvoer hieronder.';

        $this->redirect('/beheer');
    }

    public function databaseParsen(): void
    {
        $this->requireAdmin();

        try {
            $sql = SchemaParser::generateSql();
            $path = SchemaParser::writeSchemaFile($sql);

            $log = "database/xml/*.xml omgezet naar " . str_replace(APP_ROOT . '/', '', $path) . ".\n";
            $log .= "Dit bestand is NIET automatisch uitgevoerd op de database — pas het zelf toe "
                . "(de SQL is idempotent: CREATE TABLE IF NOT EXISTS / INSERT IGNORE, bestaande tabellen "
                . "en data blijven ongewijzigd, alleen wat nog ontbreekt kan worden toegevoegd).\n\n" . $sql;

            $_SESSION['beheer_output'] = $log;
            $_SESSION['flash_success'] = 'Schema.sql gegenereerd. Pas het handmatig toe op de database.';
        } catch (\Throwable $e) {
            $_SESSION['beheer_output'] = 'Fout: ' . $e->getMessage();
            $_SESSION['flash_error'] = 'Database parsen is mislukt — zie de uitvoer hieronder.';
        }

        $this->redirect('/beheer');
    }
}
