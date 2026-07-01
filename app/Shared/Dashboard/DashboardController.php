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

        $mag = [
            'tickets' => ['lezen' => $this->hasRecht('tickets'), 'schrijven' => $this->hasRecht('tickets', 'schrijven')],
            'verbeterpunten' => ['lezen' => $this->hasRecht('verbeterpunten')],
            'medewerkers' => ['lezen' => $this->hasRecht('medewerkers')],
            'voorraad' => ['lezen' => $this->hasRecht('voorraad')],
            'cyberrisicos' => ['lezen' => $this->hasRecht('cyberrisicos'), 'schrijven' => $this->hasRecht('cyberrisicos', 'schrijven')],
            'agenda' => ['lezen' => $this->hasRecht('agenda'), 'schrijven' => $this->hasRecht('agenda', 'schrijven')],
        ];

        $this->render('Views/dashboard/index', [
            'activeModule' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'mag' => $mag,
            'stats' => [
                'tickets_open' => $mag['tickets']['lezen'] ? TicketModel::countByStatus('open') : 0,
                'tickets_in_behandeling' => $mag['tickets']['lezen'] ? TicketModel::countByStatus('in_behandeling') : 0,
                'verbeterpunten' => $mag['verbeterpunten']['lezen'] ? count(VerbeterpuntModel::all()) : 0,
                'medewerkers' => $mag['medewerkers']['lezen'] ? count(MedewerkerModel::all()) : 0,
            ],
            'recenteTickets' => $mag['tickets']['lezen'] ? TicketModel::recent(5) : [],
            'voorraadOverview' => $mag['voorraad']['lezen'] ? VoorraadItemModel::countByType() : [],
            'cyberrisicosOpen' => $mag['cyberrisicos']['lezen'] ? CyberRisicoModel::countOpen() : 0,
            'cyberrisicosPerDag' => $mag['cyberrisicos']['lezen'] ? CyberRisicoModel::countLast30Days() : [],
            'cyberrisicosByDate' => $mag['cyberrisicos']['lezen'] ? CyberRisicoModel::listLast30DaysGrouped() : [],
            'afdelingen' => AfdelingModel::all(),
            'gebruikers' => UserModel::all('naam ASC'),
            'cyberCategorieen' => self::CYBER_CATEGORIE_LABELS,
            'cyberPrioriteiten' => self::CYBER_PRIORITEIT_LABELS,
        ]);
    }
}
