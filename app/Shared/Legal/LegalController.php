<?php

namespace App\Shared\Legal;

use App\Core\Controller;

class LegalController extends Controller
{
    /** Publiek toegankelijk (ook zonder in te loggen), zoals de AVG vereist. */
    public function privacybeleid(): void
    {
        ob_start();
        require APP_ROOT . '/app/Views/legal/privacybeleid.php';
        $content = ob_get_clean();

        if ($this->currentUser() !== null) {
            $this->renderWithContent($content);
            return;
        }

        $pageTitle = 'Privacybeleid';
        require APP_ROOT . '/app/Views/layouts/guest.php';
    }

    private function renderWithContent(string $content): void
    {
        $currentUser = $this->currentUser();
        $activeModule = '';
        $pageTitle = 'Privacybeleid';
        require APP_ROOT . '/app/Views/layouts/app.php';
    }
}
