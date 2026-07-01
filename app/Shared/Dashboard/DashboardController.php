<?php

namespace App\Shared\Dashboard;

use App\Core\Controller;
use App\Modules\Medewerker\Models\MedewerkerModel;
use App\Modules\Ticket\Models\TicketModel;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;
use App\Modules\Voorraad\Models\VoorraadItemModel;

class DashboardController extends Controller
{
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
        ]);
    }
}
