<?php

namespace App\Shared\Auth;

use App\Core\Controller;
use App\Shared\User\Models\UserModel;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->redirect('/');
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        ob_start();
        require APP_ROOT . '/app/Views/auth/login.php';
        $content = ob_get_clean();
        require APP_ROOT . '/app/Views/layouts/guest.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $wachtwoord = $_POST['wachtwoord'] ?? '';

        $user = UserModel::authenticate($email, $wachtwoord);

        if ($user === null) {
            $_SESSION['login_error'] = 'E-mailadres of wachtwoord is onjuist.';
            $this->redirect('/login');
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'naam' => $user['naam'],
            'rol' => $user['rol'],
            'foto' => $user['foto'] ?? null,
        ];

        $this->redirect('/');
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
        $this->redirect('/login');
    }
}
