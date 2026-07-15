<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Core\Table;
use App\Modules\Beheer\Models\LocatieModel;
use App\Shared\User\Models\UserModel;

class LocatieController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $locaties = LocatieModel::all('naam ASC');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $table = (new Table())
            ->emptyText('Geen locaties aangemaakt.')
            ->rowUrl(fn (array $l) => "/beheer/locaties/{$l['id']}/bewerken")
            ->column('naam', 'Naam', fn (array $l) => htmlspecialchars($l['naam']), ['sortable' => false])
            ->column('adres', 'Adres', fn (array $l) => htmlspecialchars($l['adres'] ?? '—'), ['sortable' => false])
            ->column('zichtbaarheid', 'Zichtbaarheid', fn (array $l) => htmlspecialchars(LocatieModel::ZICHTBAARHEID_OPTIES[$l['zichtbaarheid']] ?? $l['zichtbaarheid']), ['class' => 'col-2', 'sortable' => false])
            ->column('acties', '', fn (array $l) => '<form method="post" action="/beheer/locaties/' . (int) $l['id'] . '/verwijderen" '
                . 'onsubmit="return confirm(\'Locatie ' . htmlspecialchars(addslashes($l['naam']), ENT_QUOTES) . ' verwijderen?\')">'
                . '<button class="btn" type="submit" title="Verwijderen"><i class="bi bi-trash"></i></button></form>',
                ['class' => 'col-1', 'sortable' => false])
            ->rows($locaties);

        $content = '<div class="page-header"><div class="page-title">Locaties</div>'
            . '<a class="btn btn-primary" href="/beheer/locaties/nieuw">+ Nieuwe locatie</a></div>'
            . ($flashSuccess ? '<div class="alert alert-success">' . htmlspecialchars($flashSuccess) . '</div>' : '')
            . ($flashError ? '<div class="alert alert-error">' . htmlspecialchars($flashError) . '</div>' : '')
            . '<div class="card">' . $table->render() . '</div>';

        $this->renderContent($content, [
            'activeModule' => 'beheer',
            'pageTitle' => 'Locaties',
        ]);
    }

    public function nieuw(): void
    {
        $this->requireAdmin();

        $this->render('Modules/Beheer/Views/LocatieView/formulier', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Nieuwe locatie',
            'locatie' => null,
            'gebruikers' => UserModel::all('naam ASC'),
            'geselecteerdeGebruikers' => [],
            'oud' => ['naam' => '', 'adres' => '', 'latitude' => '', 'longitude' => '', 'zichtbaarheid' => 'iedereen'],
            'fout' => null,
        ]);
    }

    public function aanmaken(): void
    {
        $this->requireAdmin();

        [$data, $fout] = $this->valideer($_POST);
        if ($fout !== null) {
            $this->render('Modules/Beheer/Views/LocatieView/formulier', [
                'activeModule' => 'beheer',
                'pageTitle' => 'Nieuwe locatie',
                'locatie' => null,
                'gebruikers' => UserModel::all('naam ASC'),
                'geselecteerdeGebruikers' => array_map('intval', (array) ($_POST['gebruikers'] ?? [])),
                'oud' => $_POST,
                'fout' => $fout,
            ]);
            return;
        }

        $data['aanmaker_id'] = $this->currentUserId();
        $id = LocatieModel::create($data);

        if ($data['zichtbaarheid'] === 'selectie') {
            LocatieModel::setGebruikersVoorSelectie($id, array_map('intval', (array) ($_POST['gebruikers'] ?? [])));
        }

        $_SESSION['flash_success'] = "Locatie \"{$data['naam']}\" aangemaakt.";
        $this->redirect('/beheer/locaties');
    }

    public function bewerken(int $id): void
    {
        $this->requireAdmin();

        $locatie = LocatieModel::find($id);
        if ($locatie === null) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        $this->render('Modules/Beheer/Views/LocatieView/formulier', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Locatie bewerken',
            'locatie' => $locatie,
            'gebruikers' => UserModel::all('naam ASC'),
            'geselecteerdeGebruikers' => LocatieModel::gebruikersVoorSelectie($id),
            'oud' => $locatie,
            'fout' => null,
        ]);
    }

    public function opslaan(int $id): void
    {
        $this->requireAdmin();

        $locatie = LocatieModel::find($id);
        if ($locatie === null) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        [$data, $fout] = $this->valideer($_POST);
        if ($fout !== null) {
            $this->render('Modules/Beheer/Views/LocatieView/formulier', [
                'activeModule' => 'beheer',
                'pageTitle' => 'Locatie bewerken',
                'locatie' => $locatie,
                'gebruikers' => UserModel::all('naam ASC'),
                'geselecteerdeGebruikers' => array_map('intval', (array) ($_POST['gebruikers'] ?? [])),
                'oud' => $_POST,
                'fout' => $fout,
            ]);
            return;
        }

        LocatieModel::update($id, $data);
        LocatieModel::setGebruikersVoorSelectie($id, $data['zichtbaarheid'] === 'selectie'
            ? array_map('intval', (array) ($_POST['gebruikers'] ?? []))
            : []);

        $_SESSION['flash_success'] = "Locatie \"{$data['naam']}\" bijgewerkt.";
        $this->redirect('/beheer/locaties');
    }

    public function verwijderen(int $id): void
    {
        $this->requireAdmin();

        $locatie = LocatieModel::find($id);
        if ($locatie === null) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        LocatieModel::delete($id);
        $_SESSION['flash_success'] = "Locatie \"{$locatie['naam']}\" verwijderd.";
        $this->redirect('/beheer/locaties');
    }

    /** @return array{0: array, 1: string|null} */
    private function valideer(array $post, ?array $toegestaneOpties = null): array
    {
        $naam = trim((string) ($post['naam'] ?? ''));
        $adres = trim((string) ($post['adres'] ?? ''));
        $latitude = trim((string) ($post['latitude'] ?? ''));
        $longitude = trim((string) ($post['longitude'] ?? ''));
        $zichtbaarheid = (string) ($post['zichtbaarheid'] ?? '');
        $toegestaneOpties ??= LocatieModel::ZICHTBAARHEID_OPTIES;

        if ($naam === '') {
            return [[], 'Naam is verplicht.'];
        }
        if (!array_key_exists($zichtbaarheid, $toegestaneOpties)) {
            return [[], 'Ongeldige zichtbaarheid.'];
        }

        return [[
            'naam' => $naam,
            'adres' => $adres !== '' ? $adres : null,
            'latitude' => $latitude !== '' ? $latitude : null,
            'longitude' => $longitude !== '' ? $longitude : null,
            'zichtbaarheid' => $zichtbaarheid,
        ], null];
    }

    /** Zichtbaarheidsopties voor de zelfbedieningspagina "Mijn locaties" — "Iedereen" blijft aan Beheer voorbehouden. */
    private function zelfZichtbaarheidOpties(): array
    {
        return array_diff_key(LocatieModel::ZICHTBAARHEID_OPTIES, ['iedereen' => true]);
    }

    /** @param array $locatie */
    private function isEigenaarOfAdmin(array $locatie): bool
    {
        if (($this->currentUser()['rol'] ?? '') === 'admin') {
            return true;
        }

        return (int) ($locatie['aanmaker_id'] ?? 0) === (int) $this->currentUserId();
    }

    public function indexZelf(): void
    {
        $this->requireAuth();

        $locaties = LocatieModel::createdByUser((int) $this->currentUserId());
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $table = (new Table())
            ->emptyText('Je hebt nog geen locaties toegevoegd.')
            ->rowUrl(fn (array $l) => "/account/locaties/{$l['id']}/bewerken")
            ->column('naam', 'Naam', fn (array $l) => htmlspecialchars($l['naam']), ['sortable' => false])
            ->column('adres', 'Adres', fn (array $l) => htmlspecialchars($l['adres'] ?? '—'), ['sortable' => false])
            ->column('zichtbaarheid', 'Zichtbaarheid', fn (array $l) => htmlspecialchars(LocatieModel::ZICHTBAARHEID_OPTIES[$l['zichtbaarheid']] ?? $l['zichtbaarheid']), ['class' => 'col-2', 'sortable' => false])
            ->column('acties', '', fn (array $l) => '<form method="post" action="/account/locaties/' . (int) $l['id'] . '/verwijderen" '
                . 'onsubmit="return confirm(\'Locatie ' . htmlspecialchars(addslashes($l['naam']), ENT_QUOTES) . ' verwijderen?\')">'
                . '<button class="btn" type="submit" title="Verwijderen"><i class="bi bi-trash"></i></button></form>',
                ['class' => 'col-1', 'sortable' => false])
            ->rows($locaties);

        $content = '<div class="page-header"><div class="page-title">Mijn locaties</div>'
            . '<div style="display:flex;gap:8px">'
            . '<a class="btn" href="/account">Mijn profiel</a>'
            . '<a class="btn btn-primary" href="/account/locaties/nieuw">+ Nieuwe locatie</a>'
            . '</div></div>'
            . ($flashSuccess ? '<div class="alert alert-success">' . htmlspecialchars($flashSuccess) . '</div>' : '')
            . ($flashError ? '<div class="alert alert-error">' . htmlspecialchars($flashError) . '</div>' : '')
            . '<p style="color:var(--color-text-secondary);margin:0 0 12px">Locaties die je hier toevoegt kun je zelf gebruiken bij het invullen van je urenstaat, en desgewenst delen met collega\'s.</p>'
            . '<div class="card">' . $table->render() . '</div>';

        $this->renderContent($content, [
            'activeModule' => 'account',
            'pageTitle' => 'Mijn locaties',
        ]);
    }

    public function nieuwZelf(): void
    {
        $this->requireAuth();

        $this->render('Modules/Beheer/Views/LocatieView/formulier', [
            'activeModule' => 'account',
            'pageTitle' => 'Nieuwe locatie',
            'locatie' => null,
            'gebruikers' => UserModel::all('naam ASC'),
            'geselecteerdeGebruikers' => [],
            'oud' => ['naam' => '', 'adres' => '', 'latitude' => '', 'longitude' => '', 'zichtbaarheid' => 'alleen_aanmaker'],
            'fout' => null,
            'actieBasis' => '/account/locaties',
            'backUrl' => '/account/locaties',
            'zichtbaarheidOpties' => $this->zelfZichtbaarheidOpties(),
        ]);
    }

    public function aanmakenZelf(): void
    {
        $this->requireAuth();

        [$data, $fout] = $this->valideer($_POST, $this->zelfZichtbaarheidOpties());
        if ($fout !== null) {
            $this->render('Modules/Beheer/Views/LocatieView/formulier', [
                'activeModule' => 'account',
                'pageTitle' => 'Nieuwe locatie',
                'locatie' => null,
                'gebruikers' => UserModel::all('naam ASC'),
                'geselecteerdeGebruikers' => array_map('intval', (array) ($_POST['gebruikers'] ?? [])),
                'oud' => $_POST,
                'fout' => $fout,
                'actieBasis' => '/account/locaties',
                'backUrl' => '/account/locaties',
                'zichtbaarheidOpties' => $this->zelfZichtbaarheidOpties(),
            ]);
            return;
        }

        $data['aanmaker_id'] = $this->currentUserId();
        $id = LocatieModel::create($data);

        if ($data['zichtbaarheid'] === 'selectie') {
            LocatieModel::setGebruikersVoorSelectie($id, array_map('intval', (array) ($_POST['gebruikers'] ?? [])));
        }

        $_SESSION['flash_success'] = "Locatie \"{$data['naam']}\" aangemaakt.";
        $this->redirect('/account/locaties');
    }

    public function bewerkenZelf(int $id): void
    {
        $this->requireAuth();

        $locatie = LocatieModel::find($id);
        if ($locatie === null || !$this->isEigenaarOfAdmin($locatie)) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        $this->render('Modules/Beheer/Views/LocatieView/formulier', [
            'activeModule' => 'account',
            'pageTitle' => 'Locatie bewerken',
            'locatie' => $locatie,
            'gebruikers' => UserModel::all('naam ASC'),
            'geselecteerdeGebruikers' => LocatieModel::gebruikersVoorSelectie($id),
            'oud' => $locatie,
            'fout' => null,
            'actieBasis' => '/account/locaties',
            'backUrl' => '/account/locaties',
            'zichtbaarheidOpties' => $this->zelfZichtbaarheidOpties(),
        ]);
    }

    public function opslaanZelf(int $id): void
    {
        $this->requireAuth();

        $locatie = LocatieModel::find($id);
        if ($locatie === null || !$this->isEigenaarOfAdmin($locatie)) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        [$data, $fout] = $this->valideer($_POST, $this->zelfZichtbaarheidOpties());
        if ($fout !== null) {
            $this->render('Modules/Beheer/Views/LocatieView/formulier', [
                'activeModule' => 'account',
                'pageTitle' => 'Locatie bewerken',
                'locatie' => $locatie,
                'gebruikers' => UserModel::all('naam ASC'),
                'geselecteerdeGebruikers' => array_map('intval', (array) ($_POST['gebruikers'] ?? [])),
                'oud' => $_POST,
                'fout' => $fout,
                'actieBasis' => '/account/locaties',
                'backUrl' => '/account/locaties',
                'zichtbaarheidOpties' => $this->zelfZichtbaarheidOpties(),
            ]);
            return;
        }

        LocatieModel::update($id, $data);
        LocatieModel::setGebruikersVoorSelectie($id, $data['zichtbaarheid'] === 'selectie'
            ? array_map('intval', (array) ($_POST['gebruikers'] ?? []))
            : []);

        $_SESSION['flash_success'] = "Locatie \"{$data['naam']}\" bijgewerkt.";
        $this->redirect('/account/locaties');
    }

    public function verwijderenZelf(int $id): void
    {
        $this->requireAuth();

        $locatie = LocatieModel::find($id);
        if ($locatie === null || !$this->isEigenaarOfAdmin($locatie)) {
            http_response_code(404);
            echo 'Locatie niet gevonden.';
            return;
        }

        LocatieModel::delete($id);
        $_SESSION['flash_success'] = "Locatie \"{$locatie['naam']}\" verwijderd.";
        $this->redirect('/account/locaties');
    }
}
