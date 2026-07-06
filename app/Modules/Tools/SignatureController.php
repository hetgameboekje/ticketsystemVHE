<?php

namespace App\Modules\Tools;

use App\Core\Controller;
use App\Modules\Tools\Lib\SignatureIcons;
use App\Modules\Tools\Lib\SignatureRenderer;
use App\Modules\Tools\Models\EmailSignatureModel;
use App\Modules\Tools\Models\SignatureLogoModel;

class SignatureController extends Controller
{
    private string $logoDir;

    public function __construct()
    {
        $this->logoDir = APP_ROOT . '/public/uploads/tools/logos';
    }

    public function index(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/SignatureView/index', [
            'activeModule' => 'tools',
            'pageTitle' => 'Handtekeningen',
            'signatures' => EmailSignatureModel::all(),
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->renderForm(null);
    }

    public function store(): void
    {
        $this->requireAuth();

        $name = trim($_POST['name'] ?? '');
        $lines = $this->linesFromPost($_POST['lines'] ?? []);

        if ($name === '') {
            $_SESSION['flash_error'] = 'Naam is verplicht.';
            $this->redirect('/tools/handtekeningen/nieuw');
        }

        $id = EmailSignatureModel::createWithLines($name, $lines);
        $this->redirect("/tools/handtekeningen/{$id}/bewerken?saved=1");
    }

    public function edit(int $id): void
    {
        $this->requireAuth();
        $this->renderForm($id);
    }

    public function update(int $id): void
    {
        $this->requireAuth();

        if (EmailSignatureModel::find($id) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $lines = $this->linesFromPost($_POST['lines'] ?? []);

        if ($name === '') {
            $_SESSION['flash_error'] = 'Naam is verplicht.';
            $this->redirect("/tools/handtekeningen/{$id}/bewerken");
        }

        EmailSignatureModel::updateWithLines($id, $name, $lines);
        $this->redirect("/tools/handtekeningen/{$id}/bewerken?saved=1");
    }

    public function destroy(int $id): void
    {
        $this->requireAuth();
        EmailSignatureModel::delete($id);
        $this->redirect('/tools/handtekeningen');
    }

    /** Logo toevoegen aan de gedeelde logo-bibliotheek (herbruikbaar over meerdere handtekeningen). */
    public function uploadLogo(): void
    {
        $this->requireAuth();

        $naam = trim($_POST['logo_naam'] ?? '');
        $file = $_FILES['logo_bestand'] ?? null;
        $breedte = max(20, (int) ($_POST['logo_breedte'] ?? 200));

        if ($naam === '' || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Naam en een geldig logobestand zijn verplicht.';
            $this->redirect($_POST['terug_naar'] ?? '/tools/handtekeningen');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'svg', 'gif'], true)) {
            $_SESSION['flash_error'] = 'Alleen PNG, JPG, GIF of SVG wordt ondersteund voor logo\'s.';
            $this->redirect($_POST['terug_naar'] ?? '/tools/handtekeningen');
        }

        if (!is_dir($this->logoDir)) {
            mkdir($this->logoDir, 0777, true);
        }

        $storedName = uniqid('logo_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $this->logoDir . '/' . $storedName)) {
            $_SESSION['flash_error'] = 'Het logo kon niet worden opgeslagen.';
            $this->redirect($_POST['terug_naar'] ?? '/tools/handtekeningen');
        }

        SignatureLogoModel::create(['naam' => $naam, 'bestand' => $storedName, 'breedte' => $breedte]);

        $this->redirect($_POST['terug_naar'] ?? '/tools/handtekeningen');
    }

    public function destroyLogo(int $id): void
    {
        $this->requireAuth();

        $logo = SignatureLogoModel::find($id);
        if ($logo !== null) {
            $path = $this->logoDir . '/' . $logo['bestand'];
            if (is_file($path)) {
                unlink($path);
            }
            SignatureLogoModel::delete($id);
        }

        $this->redirect($_POST['terug_naar'] ?? '/tools/handtekeningen');
    }

    private function renderForm(?int $id): void
    {
        $signature = $id !== null ? EmailSignatureModel::findWithLines($id) : null;

        if ($id !== null && $signature === null) {
            http_response_code(404);
            echo 'Handtekening niet gevonden.';
            return;
        }

        $this->render('Modules/Tools/Views/SignatureView/form', [
            'activeModule' => 'tools',
            'pageTitle' => $id !== null ? 'Handtekening bewerken' : 'Nieuwe handtekening',
            'signature' => $signature,
            'icons' => SignatureIcons::ICONS,
            'logos' => SignatureLogoModel::all(),
            'previewHtml' => $signature ? SignatureRenderer::render($signature['lines']) : '',
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $rawLines
     * @return array<int, array<string, mixed>>
     */
    private function linesFromPost(array $rawLines): array
    {
        $lines = [];

        foreach ($rawLines as $rawLine) {
            $type = $rawLine['type'] ?? 'text';
            $type = in_array($type, ['icon', 'logo'], true) ? $type : 'text';

            if ($type === 'logo') {
                $logoId = (int) ($rawLine['logo_id'] ?? 0);
                if ($logoId <= 0 || SignatureLogoModel::find($logoId) === null) {
                    continue;
                }

                $lines[] = [
                    'type' => 'logo',
                    'logo_id' => $logoId,
                    'href' => trim($rawLine['href'] ?? ''),
                ];
                continue;
            }

            $text = trim($rawLine['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $line = [
                'type' => $type,
                'text' => $text,
                'bold' => !empty($rawLine['bold']),
                'href' => trim($rawLine['href'] ?? ''),
            ];

            if ($type === 'icon') {
                $icon = $rawLine['icon'] ?? '';
                $line['icon'] = array_key_exists($icon, SignatureIcons::ICONS) ? $icon : '';
            }

            $lines[] = $line;
        }

        return $lines;
    }
}
