<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Shared\Log\Models\PaginaBezoekModel;
use App\Shared\User\Models\UserModel;

class LogController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'ip_adres' => $_GET['ip_adres'] ?? '',
            'q' => trim($_GET['q'] ?? ''),
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $resultaat = PaginaBezoekModel::search($filters, $page);

        $this->render('Modules/Beheer/Views/LogView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Paginabezoeken',
            'bezoeken' => $resultaat['items'],
            'pagination' => $resultaat,
            'gebruikers' => UserModel::all('naam ASC'),
            'ipAdressen' => PaginaBezoekModel::distinctIpAdressen(),
            'filters' => $filters,
        ]);
    }
}
