<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HardwareUitgaveController;
use App\Controllers\KennisbankController;
use App\Controllers\MedewerkerController;
use App\Controllers\ReflectieController;
use App\Controllers\TicketController;
use App\Controllers\TicketLogController;
use App\Controllers\VerbeterpuntController;
use App\Core\Router;

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

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
