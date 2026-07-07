<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Shared\Auth\Models\LoginAttemptModel;
use App\Shared\User\Models\UserModel;

class BeveiligingController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'alleen_verdacht' => $_GET['alleen_verdacht'] ?? '',
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $resultaat = LoginAttemptModel::search($filters, $page);

        $this->render('Modules/Beheer/Views/BeveiligingView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Beveiliging — inlogpogingen',
            'pogingen' => $resultaat['items'],
            'pagination' => $resultaat,
            'gebruikers' => UserModel::all('naam ASC'),
            'filters' => $filters,
        ]);
    }
}
