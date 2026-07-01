<?php

namespace App\Shared\Dashboard;

use App\Core\Controller;
use App\Modules\CyberRisico\Models\CyberRisicoModel;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Ticket\Models\TicketModel;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;
use App\Modules\Voorraad\Models\VoorraadItemModel;
use App\Shared\Afdeling\Models\AfdelingModel;
use App\Shared\User\Models\UserModel;

class DashboardController extends Controller
{
    private const CYBER_CATEGORIE_LABELS = [
        'fysieke_toegang' => 'Fysieke toegang',
        'social_engineering' => 'Social engineering',
        'onveilige_opslag' => 'Onveilige opslag',
        'papieren_informatie' => 'Papieren informatie',
        'device_exposure' => 'Device exposure',
        'overig' => 'Overig',
    ];

    private const CYBER_PRIORITEIT_LABELS = [
        'laag' => 'Laag',
        'middel' => 'Middel',
        'hoog' => 'Hoog',
        'kritiek' => 'Kritiek',
    ];

    public function index(): void
    {
        $this->requireAuth();

        $this->render('Views/dashboard/index', [
            'activeModule' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => [
                'tickets_open' => TicketModel::countByStatus('open'),
                'tickets_in_behandeling' => TicketModel::countByStatus('in_behandeling'),
                'verbeterpunten' => count(VerbeterpuntModel::all()),
                'medewerkers' => count(MedewerkerModel::all()),
            ],
            'recenteTickets' => TicketModel::recent(5),
            'voorraadOverview' => VoorraadItemModel::countByType(),
            'cyberrisicosOpen' => CyberRisicoModel::countOpen(),
            'cyberrisicosPerDag' => CyberRisicoModel::countLast30Days(),
            'cyberrisicosByDate' => CyberRisicoModel::listLast30DaysGrouped(),
            'afdelingen' => AfdelingModel::all(),
            'gebruikers' => UserModel::all('naam ASC'),
            'cyberCategorieen' => self::CYBER_CATEGORIE_LABELS,
            'cyberPrioriteiten' => self::CYBER_PRIORITEIT_LABELS,
        ]);
    }
}
