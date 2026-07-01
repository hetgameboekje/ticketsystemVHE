<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Modules\Account\AccountController;
use App\Modules\Agenda\AgendaController;
use App\Modules\Beheer\BeheerController;
use App\Modules\Beheer\LogController;
use App\Modules\Beheer\RechtenController;
use App\Modules\CyberRisico\CyberRisicoController;
use App\Modules\HardwareUitgave\HardwareUitgaveController;
use App\Modules\Kennisbank\KennisbankController;
use App\Modules\Medewerker\MedewerkerController;
use App\Modules\Printer\PrinterController;
use App\Modules\Reflectie\ReflectieController;
use App\Modules\Ticket\TicketController;
use App\Modules\Ticket\TicketLogController;
use App\Modules\Uitgifte\UitgifteController;
use App\Modules\Verbeterpunt\VerbeterpuntController;
use App\Modules\Voorraad\VoorraadController;
use App\Shared\Auth\AuthController;
use App\Shared\Dashboard\DashboardController;
use App\Shared\Legal\LegalController;

$router = new Router();

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/', [DashboardController::class, 'index']);

$modules = [
    'tickets' => TicketController::class,
    'verbeterpunten' => VerbeterpuntController::class,
    'reflecties' => ReflectieController::class,
    'kennisbank' => KennisbankController::class,
    'hardware-uitgaven' => HardwareUitgaveController::class,
    'medewerkers' => MedewerkerController::class,
    'voorraad' => VoorraadController::class,
    'printers' => PrinterController::class,
    'cyberrisicos' => CyberRisicoController::class,
];

foreach ($modules as $routeBase => $controller) {
    $router->get("/{$routeBase}", [$controller, 'index']);
    $router->get("/{$routeBase}/create", [$controller, 'create']);
    $router->post("/{$routeBase}", [$controller, 'store']);
    $router->get("/{$routeBase}/{id}", [$controller, 'show']);
    $router->get("/{$routeBase}/{id}/edit", [$controller, 'edit']);
    $router->post("/{$routeBase}/{id}", [$controller, 'update']);
    $router->post("/{$routeBase}/{id}/verwijderen", [$controller, 'destroy']);
}

$router->post('/tickets/{id}/log', [TicketLogController::class, 'store']);
$router->get('/tickets/export', [TicketController::class, 'export']);
$router->post('/tickets/import', [TicketController::class, 'import']);

$router->get('/voorraad/{id}/barcode', [VoorraadController::class, 'barcode']);

$router->get('/uitgiften', [UitgifteController::class, 'index']);
$router->get('/uitgiften/namen', [UitgifteController::class, 'namen']);
$router->get('/uitgiften/items', [UitgifteController::class, 'items']);
$router->get('/uitgiften/create', [UitgifteController::class, 'create']);
$router->post('/uitgiften', [UitgifteController::class, 'store']);
$router->get('/uitgiften/{id}', [UitgifteController::class, 'show']);
$router->post('/uitgiften/{id}/retour', [UitgifteController::class, 'retour']);

$router->get('/agenda', [AgendaController::class, 'index']);
$router->get('/agenda/events', [AgendaController::class, 'events']);
$router->post('/agenda', [AgendaController::class, 'store']);
$router->post('/agenda/{id}', [AgendaController::class, 'update']);
$router->post('/agenda/{id}/verwijderen', [AgendaController::class, 'destroy']);

$router->get('/account', [AccountController::class, 'profiel']);
$router->get('/account/bewerken', [AccountController::class, 'bewerken']);
$router->post('/account', [AccountController::class, 'bijwerken']);

$router->get('/beheer', [BeheerController::class, 'index']);
$router->post('/beheer/git-pull', [BeheerController::class, 'gitPull']);
$router->post('/beheer/database-parsen', [BeheerController::class, 'databaseParsen']);

$router->get('/beheer/rechten', [RechtenController::class, 'index']);
$router->get('/beheer/rechten/nieuw', [RechtenController::class, 'aanmaken']);
$router->post('/beheer/rechten', [RechtenController::class, 'opslaan']);
$router->get('/beheer/rechten/{id}', [RechtenController::class, 'bewerken']);
$router->post('/beheer/rechten/{id}', [RechtenController::class, 'bijwerken']);
$router->post('/beheer/rechten/{id}/gebruiker', [RechtenController::class, 'gebruikerBijwerken']);
$router->post('/beheer/rechten/{id}/wachtwoord', [RechtenController::class, 'wachtwoordWijzigen']);
$router->post('/beheer/rechten/{id}/verwijderen', [RechtenController::class, 'verwijderen']);

$router->get('/beheer/log', [LogController::class, 'index']);

$router->get('/privacybeleid', [LegalController::class, 'privacybeleid']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
