<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Medewerker;
use App\Models\Ticket;
use App\Models\Verbeterpunt;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->render('dashboard/index', [
            'activeModule' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'stats' => [
                'tickets_open' => Ticket::countByStatus('open'),
                'tickets_in_behandeling' => Ticket::countByStatus('in_behandeling'),
                'verbeterpunten' => count(Verbeterpunt::all()),
                'medewerkers' => count(Medewerker::all()),
            ],
            'recenteTickets' => Ticket::recent(5),
        ]);
    }
}
