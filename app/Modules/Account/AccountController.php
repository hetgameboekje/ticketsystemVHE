<?php

namespace App\Modules\Account;

use App\Core\Controller;
use App\Shared\User\Models\UserModel;

class AccountController extends Controller
{
    private const TOEGESTANE_EXTENSIES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const MAX_BESTANDSGROOTTE = 2 * 1024 * 1024; // 2 MB

    public function profiel(): void
    {
        $this->requireAuth();
        $user = UserModel::find((int) $this->currentUserId());

        $this->render('Modules/Account/Views/AccountView/profiel', [
            'user' => $user,
            'activeModule' => 'account',
            'pageTitle' => 'Mijn profiel',
        ]);
    }

    public function bewerken(): void
    {
        $this->requireAuth();
        $user = UserModel::find((int) $this->currentUserId());

        $this->render('Modules/Account/Views/AccountView/bewerken', [
            'user' => $user,
            'activeModule' => 'account',
            'pageTitle' => 'Profiel bewerken',
        ]);
    }

    public function bijwerken(): void
    {
        $this->requireAuth();
        $userId = (int) $this->currentUserId();
        $user = UserModel::find($userId);

        $naam = trim($_POST['naam'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($naam === '' || $email === '') {
            $_SESSION['flash_error'] = 'Naam en e-mailadres zijn verplicht.';
            $this->redirect('/account/bewerken');
        }

        $bestaand = UserModel::findByEmail($email);
        if ($bestaand !== null && (int) $bestaand['id'] !== $userId) {
            $_SESSION['flash_error'] = 'Dit e-mailadres is al in gebruik door een andere gebruiker.';
            $this->redirect('/account/bewerken');
        }

        $data = ['naam' => $naam, 'email' => $email];

        $nieuwWachtwoord = $_POST['wachtwoord'] ?? '';
        if ($nieuwWachtwoord !== '') {
            if (strlen($nieuwWachtwoord) < 8) {
                $_SESSION['flash_error'] = 'Nieuw wachtwoord moet minimaal 8 tekens zijn.';
                $this->redirect('/account/bewerken');
            }
            $data['wachtwoord_hash'] = password_hash($nieuwWachtwoord, PASSWORD_DEFAULT);
        }

        if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoPad = $this->verwerkFotoUpload($userId, $_FILES['foto']);
            if ($fotoPad === null) {
                $this->redirect('/account/bewerken');
            }
            $data['foto'] = $fotoPad;

            if (!empty($user['foto'])) {
                $oudPad = APP_ROOT . '/public' . $user['foto'];
                if (is_file($oudPad)) {
                    unlink($oudPad);
                }
            }
        }

        UserModel::update($userId, $data);

        $_SESSION['user']['naam'] = $naam;
        $_SESSION['user']['foto'] = $data['foto'] ?? ($user['foto'] ?? null);

        $_SESSION['flash_success'] = 'Profiel bijgewerkt.';
        $this->redirect('/account');
    }

    private function verwerkFotoUpload(int $userId, array $file): ?string
    {
        if ($file['size'] > self::MAX_BESTANDSGROOTTE) {
            $_SESSION['flash_error'] = 'Foto is te groot (max 2 MB).';
            return null;
        }

        $extensie = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extensie, self::TOEGESTANE_EXTENSIES, true)) {
            $_SESSION['flash_error'] = 'Ongeldig bestandstype. Toegestaan: jpg, png, gif, webp.';
            return null;
        }

        $uploadDir = APP_ROOT . '/public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $bestandsnaam = 'user-' . $userId . '-' . time() . '.' . $extensie;
        $doelpad = $uploadDir . '/' . $bestandsnaam;

        if (!move_uploaded_file($file['tmp_name'], $doelpad)) {
            $_SESSION['flash_error'] = 'Uploaden van foto is mislukt.';
            return null;
        }

        return '/uploads/avatars/' . $bestandsnaam;
    }
}
