<?php

namespace App\Core;

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
