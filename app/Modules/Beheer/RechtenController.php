<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Core\Table;
use App\Shared\Rechten\Models\RechtenModel;
use App\Shared\User\Models\UserModel;

class RechtenController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $gebruikers = UserModel::all('naam ASC');

        $table = (new Table())
            ->emptyText('Geen gebruikers gevonden.')
            ->rowUrl(fn (array $g) => $g['rol'] === 'admin' ? 'javascript:void(0)' : '/beheer/rechten/' . (int) $g['id'])
            ->column('naam', 'Naam', fn (array $g) => htmlspecialchars($g['naam']), ['class' => 'col-3', 'sortable' => false])
            ->column('email', 'E-mailadres', fn (array $g) => htmlspecialchars($g['email']), ['sortable' => false])
            ->column('rol', 'Rol', fn (array $g) => htmlspecialchars(ucfirst($g['rol'])), ['class' => 'col-2', 'sortable' => false])
            ->rows($gebruikers);

        $content = '<div class="page-header"><div class="page-title">Rechten</div></div>'
            . '<div class="card">' . $table->render() . '</div>';

        $this->renderContent($content, [
            'activeModule' => 'beheer',
            'pageTitle' => 'Rechten',
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
