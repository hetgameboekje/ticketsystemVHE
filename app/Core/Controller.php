<?php

namespace App\Core;

use App\Shared\ApiKey\Models\ApiKeyModel;
use App\Shared\Rechten\Models\RechtenModel;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = APP_ROOT . "/app/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View niet gevonden: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $currentUser = $this->currentUser();
        $navRechten = $this->navRechten();
        $csrfToken = Csrf::token();
        require APP_ROOT . '/app/Views/layouts/app.php';
    }

    /** Zoals render(), maar met kant-en-klare HTML in plaats van een view-bestand. */
    protected function renderContent(string $content, array $data = []): void
    {
        extract($data);

        $currentUser = $this->currentUser();
        $navRechten = $this->navRechten();
        $csrfToken = Csrf::token();
        require APP_ROOT . '/app/Views/layouts/app.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function currentUserId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /** Alleen toegankelijk voor gebruikers met rol 'admin' — gebruikt door Beheer/Rechten/Log. */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($this->currentUser()['rol'] ?? '') !== 'admin') {
            $this->forbidden();
        }
    }

    /**
     * Controleert de granulaire rechtenmatrix voor een module. Admins hebben altijd
     * volledige toegang; andere gebruikers hebben expliciet toegekende rechten nodig.
     */
    protected function requirePermission(string $module, string $actie): void
    {
        $this->requireAuth();

        if (($this->currentUser()['rol'] ?? '') === 'admin') {
            return;
        }

        if (!RechtenModel::has((int) $this->currentUserId(), $module, $actie)) {
            $this->forbidden();
        }
    }

    /** Niet-blokkerende variant van requirePermission(), voor UI die rechten moet filteren i.p.v. afdwingen. */
    protected function hasRecht(string $module, string $actie = 'lezen'): bool
    {
        if (($this->currentUser()['rol'] ?? '') === 'admin') {
            return true;
        }

        $userId = $this->currentUserId();
        if ($userId === null) {
            return false;
        }

        return RechtenModel::has($userId, $module, $actie);
    }

    /** @return array<string, bool> module => mag lezen, gebruikt om navigatie en dashboard te filteren. */
    protected function navRechten(): array
    {
        if ($this->currentUser() === null) {
            return [];
        }

        $out = [];
        foreach (RechtenModel::MODULES as $module => $label) {
            $out[$module] = $this->hasRecht($module, 'lezen');
        }

        return $out;
    }

    /**
     * Auth voor machine-to-machine endpoints (e-mailintake, Taakplanner-taken): geen sessie, maar een
     * API-sleutel (header X-Api-Key of POST-veld api_key) die de gevraagde scope moet hebben. Sleutels
     * worden beheerd via Beheer > API-sleutels (zie App\Shared\ApiKey\Models\ApiKeyModel).
     */
    protected function heeftApiSleutelMetScope(string $scope): bool
    {
        $meegegeven = $_SERVER['HTTP_X_API_KEY'] ?? ($_POST['api_key'] ?? '');
        if (!is_string($meegegeven) || $meegegeven === '') {
            return false;
        }

        return ApiKeyModel::vindActieveMetScope($meegegeven, $scope) !== null;
    }

    protected function forbidden(): void
    {
        http_response_code(403);
        $this->render('Views/errors/403', [
            'activeModule' => '',
            'pageTitle' => 'Geen toegang',
        ]);
        exit;
    }
}
