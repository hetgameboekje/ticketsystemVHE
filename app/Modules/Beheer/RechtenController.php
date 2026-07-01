<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Shared\Rechten\Models\RechtenModel;
use App\Shared\User\Models\UserModel;

class RechtenController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $this->render('Modules/Beheer/Views/RechtenView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Rechten',
            'gebruikers' => UserModel::all('naam ASC'),
        ]);
    }

    public function bewerken(int $id): void
    {
        $this->requireAdmin();

        $gebruiker = UserModel::find($id);
        if ($gebruiker === null) {
            http_response_code(404);
            echo 'Gebruiker niet gevonden.';
            return;
        }

        $this->render('Modules/Beheer/Views/RechtenView/bewerken', [
            'activeModule' => 'beheer',
            'pageTitle' => 'Rechten — ' . $gebruiker['naam'],
            'gebruiker' => $gebruiker,
            'modules' => RechtenModel::MODULES,
            'rechten' => RechtenModel::forUser($id),
        ]);
    }

    public function bijwerken(int $id): void
    {
        $this->requireAdmin();

        $gebruiker = UserModel::find($id);
        if ($gebruiker === null) {
            http_response_code(404);
            echo 'Gebruiker niet gevonden.';
            return;
        }

        $moduleRechten = [];
        foreach (array_keys(RechtenModel::MODULES) as $module) {
            $moduleRechten[$module] = [
                'lezen' => !empty($_POST['rechten'][$module]['lezen']),
                'schrijven' => !empty($_POST['rechten'][$module]['schrijven']),
                'verwijderen' => !empty($_POST['rechten'][$module]['verwijderen']),
            ];
        }

        RechtenModel::setForUser($id, $moduleRechten);

        $_SESSION['flash_success'] = "Rechten van {$gebruiker['naam']} bijgewerkt.";
        $this->redirect('/beheer/rechten');
    }
}
