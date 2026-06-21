<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

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

        $user = User::authenticate($email, $wachtwoord);

        if ($user === null) {
            $_SESSION['login_error'] = 'E-mailadres of wachtwoord is onjuist.';
            $this->redirect('/login');
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'naam' => $user['naam'],
            'rol' => $user['rol'],
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
