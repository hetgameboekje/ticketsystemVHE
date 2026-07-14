<?php

namespace App\Shared\Auth;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\DevSync;
use App\Core\Mailer;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Shared\Auth\Models\LoginAttemptModel;
use App\Shared\User\Models\UserModel;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (DevSync::isEnabled()) {
            try {
                foreach (DevSync::run() as $line) {
                    error_log('[DevSync] ' . $line);
                }
            } catch (\Throwable $e) {
                error_log('[DevSync] mislukt: ' . $e->getMessage());
            }
        }

        if (!empty($_SESSION['user'])) {
            $this->redirect('/');
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        ob_start();
        require APP_ROOT . '/app/Views/auth/login.php';
        $content = ob_get_clean();
        $csrfToken = Csrf::token();
        require APP_ROOT . '/app/Views/layouts/guest.php';
    }

    /** Max. mislukte pogingen binnen LOCKOUT_MINUTEN voordat een e-mailadres tijdelijk wordt geblokkeerd. */
    private const MAX_MISLUKTE_POGINGEN = 5;
    private const LOCKOUT_MINUTEN = 15;

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $wachtwoord = $_POST['wachtwoord'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $mislukteAantal = LoginAttemptModel::recentFailedCount($email, self::LOCKOUT_MINUTEN);
        if ($mislukteAantal >= self::MAX_MISLUKTE_POGINGEN) {
            $this->logPoging($email, null, $ip, $userAgent, success: false, isNewIp: false);
            // Alleen bij het moment dat de lockout ingaat waarschuwen, niet bij elke volgende
            // poging tijdens de lockout (anders loopt de mailbox vol zolang iemand blijft proberen).
            if ($mislukteAantal === self::MAX_MISLUKTE_POGINGEN) {
                $this->waarschuwVerdachteLogin($email, $ip);
            }
            $_SESSION['login_error'] = 'Te veel mislukte inlogpogingen. Probeer het over enkele minuten opnieuw.';
            $this->redirect('/login');
        }

        try {
            $user = UserModel::authenticate($email, $wachtwoord);
        } catch (\PDOException $e) {
            error_log('[Login] databaseverbinding mislukt: ' . $e->getMessage());
            $_SESSION['login_error'] = 'Inloggen is momenteel niet mogelijk (databaseverbinding mislukt). Probeer het later opnieuw.';
            $this->redirect('/login');
        }

        if ($user === null) {
            $this->logPoging($email, null, $ip, $userAgent, success: false, isNewIp: false);
            $_SESSION['login_error'] = 'E-mailadres of wachtwoord is onjuist.';
            $this->redirect('/login');
        }

        $userId = (int) $user['id'];
        $isNewIp = LoginAttemptModel::hasAnyPriorSuccessfulLogin($userId)
            && !LoginAttemptModel::hasSuccessfulLoginFromIp($userId, $ip);
        $this->logPoging($email, $userId, $ip, $userAgent, success: true, isNewIp: $isNewIp);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'naam' => $user['naam'],
            'rol' => $user['rol'],
            'foto' => $user['foto'] ?? null,
            'afdeling_id' => MedewerkerModel::afdelingIdVoorUser((int) $user['id']),
        ];

        $this->redirect('/');
    }

    private function logPoging(string $email, ?int $userId, string $ip, ?string $userAgent, bool $success, bool $isNewIp): void
    {
        try {
            LoginAttemptModel::record($email, $userId, $ip, $userAgent, $success, $isNewIp);
        } catch (\Throwable $e) {
            // Logging mag de normale afhandeling van het inloggen nooit breken.
        }
    }

    private function waarschuwVerdachteLogin(string $email, string $ip): void
    {
        $config = require APP_ROOT . '/config/config.php';
        $admin = $config['mail']['admin_address'];

        if ($admin === '') {
            return;
        }

        try {
            Mailer::verstuur(
                $admin,
                'Ticketsysteem VHE: verdachte inlogpogingen gedetecteerd',
                "Voor het account {$email} zijn binnen " . self::LOCKOUT_MINUTEN . " minuten "
                . self::MAX_MISLUKTE_POGINGEN . " mislukte inlogpogingen geregistreerd vanaf IP-adres {$ip}.<br>"
                . 'Het account is tijdelijk geblokkeerd. Bekijk Beheer &gt; Beveiliging voor details.'
            );
        } catch (\Throwable $e) {
            // Als zelfs de waarschuwing niet verstuurd kan worden, is er niets meer te doen vanuit hier.
        }
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
        $this->redirect('/login');
    }
}
