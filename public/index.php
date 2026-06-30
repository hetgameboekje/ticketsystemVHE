<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Modules\HardwareUitgave\HardwareUitgaveController;
use App\Modules\Kennisbank\KennisbankController;
use App\Modules\Medewerker\MedewerkerController;
use App\Modules\Reflectie\ReflectieController;
use App\Modules\Ticket\TicketController;
use App\Modules\Ticket\TicketLogController;
use App\Modules\Verbeterpunt\VerbeterpuntController;
use App\Shared\Auth\AuthController;
use App\Shared\Dashboard\DashboardController;

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

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
