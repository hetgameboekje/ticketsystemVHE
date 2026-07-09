<?php

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Modules\Account\AccountController;
use App\Modules\Agenda\AgendaController;
use App\Modules\Beheer\ApiSleutelController;
use App\Modules\Beheer\BeheerController;
use App\Modules\Beheer\BeveiligingController;
use App\Modules\Beheer\EmailQueueController;
use App\Modules\Beheer\ExportController;
use App\Modules\Beheer\LogController;
use App\Modules\Beheer\RechtenController;
use App\Modules\CyberRisico\CyberRisicoController;
use App\Modules\CyberRisico\CyberRisicoLogController;
use App\Modules\Device\DeviceController;
use App\Modules\HardwareUitgave\HardwareUitgaveController;
use App\Modules\Kennisbank\KennisbankController;
use App\Modules\Kennisbank\KennisbankLogController;
use App\Modules\Medewerker\MedewerkerController;
use App\Modules\Printer\PrinterController;
use App\Modules\Reflectie\ReflectieController;
use App\Modules\Reflectie\ReflectieLogController;
use App\Modules\Schijfgebruik\SchijfgebruikController;
use App\Modules\Ticket\TicketController;
use App\Modules\Ticket\TicketEmailIntakeController;
use App\Modules\Ticket\TicketLogController;
use App\Modules\Tools\PhonebookController;
use App\Modules\Tools\SignatureController;
use App\Modules\Tools\ToolsController;
use App\Modules\Uitgifte\UitgifteController;
use App\Modules\Verbeterpunt\VerbeterpuntController;
use App\Modules\Verbeterpunt\VerbeterpuntLogController;
use App\Modules\Voorraad\VoorraadController;
use App\Shared\Automation\AutomationController;
use App\Shared\Auth\AuthController;
use App\Shared\Dashboard\DashboardController;
use App\Shared\Legal\LegalController;
use App\Shared\Overview\OverviewController;

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
    'apparaten' => DeviceController::class,
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
$router->post('/tickets/{id}/kennisbank', [TicketController::class, 'kennisbankKoppel']);
$router->post('/tickets/{id}/kennisbank/{id}/verwijderen', [TicketController::class, 'kennisbankOntkoppel']);
$router->post('/api/tickets/vanuit-email', [TicketEmailIntakeController::class, 'store']);
$router->post('/api/tickets/vanuit-aca-email', [TicketEmailIntakeController::class, 'storeAcaUpdate']);
$router->post('/api/email-queue/verwerken', [AutomationController::class, 'emailQueueVerwerken']);
$router->post('/api/tickets/herinneringen', [AutomationController::class, 'ticketHerinneringenGenereren']);

$router->get('/ict', [OverviewController::class, 'ict']);
$router->get('/crm', [OverviewController::class, 'crm']);

$router->get('/tools', [ToolsController::class, 'index']);

$router->get('/tools/telefoonlijst', [PhonebookController::class, 'index']);
$router->post('/tools/telefoonlijst', [PhonebookController::class, 'store']);
$router->get('/tools/telefoonlijst/{id}', [PhonebookController::class, 'show']);
$router->get('/tools/telefoonlijst/{id}/download', [PhonebookController::class, 'download']);
$router->post('/tools/telefoonlijst/{id}/verwijderen', [PhonebookController::class, 'destroy']);

$router->get('/tools/handtekeningen', [SignatureController::class, 'index']);
$router->get('/tools/handtekeningen/nieuw', [SignatureController::class, 'create']);
$router->post('/tools/handtekeningen', [SignatureController::class, 'store']);
$router->get('/tools/handtekeningen/{id}/bewerken', [SignatureController::class, 'edit']);
$router->post('/tools/handtekeningen/{id}', [SignatureController::class, 'update']);
$router->post('/tools/handtekeningen/{id}/verwijderen', [SignatureController::class, 'destroy']);
$router->post('/tools/handtekeningen/logos', [SignatureController::class, 'uploadLogo']);
$router->post('/tools/handtekeningen/logos/{id}/verwijderen', [SignatureController::class, 'destroyLogo']);
$router->get('/tickets/export', [TicketController::class, 'export']);
$router->post('/tickets/import', [TicketController::class, 'import']);

$router->post('/verbeterpunten/{id}/log', [VerbeterpuntLogController::class, 'store']);
$router->post('/reflecties/{id}/log', [ReflectieLogController::class, 'store']);
$router->post('/kennisbank/{id}/log', [KennisbankLogController::class, 'store']);
$router->post('/kennisbank/{id}/log/volgorde', [KennisbankLogController::class, 'reorder']);
$router->post('/cyberrisicos/{id}/log', [CyberRisicoLogController::class, 'store']);

$router->get('/voorraad/{id}/barcode', [VoorraadController::class, 'barcode']);

$router->get('/schijfgebruik', [SchijfgebruikController::class, 'index']);
$router->post('/schijfgebruik/import', [SchijfgebruikController::class, 'upload']);

$router->get('/medewerkers/login-check', [MedewerkerController::class, 'loginCheck']);

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
$router->post('/beheer/database-toepassen', [BeheerController::class, 'databaseToepassen']);

$router->get('/beheer/rechten', [RechtenController::class, 'index']);
$router->get('/beheer/rechten/nieuw', [RechtenController::class, 'aanmaken']);
$router->post('/beheer/rechten', [RechtenController::class, 'opslaan']);
$router->get('/beheer/rechten/{id}', [RechtenController::class, 'bewerken']);
$router->post('/beheer/rechten/{id}', [RechtenController::class, 'bijwerken']);
$router->post('/beheer/rechten/{id}/gebruiker', [RechtenController::class, 'gebruikerBijwerken']);
$router->post('/beheer/rechten/{id}/wachtwoord', [RechtenController::class, 'wachtwoordWijzigen']);
$router->post('/beheer/rechten/{id}/verwijderen', [RechtenController::class, 'verwijderen']);
$router->post('/beheer/rechten/{id}/heractiveren', [RechtenController::class, 'heractiveren']);

$router->get('/beheer/api-sleutels', [ApiSleutelController::class, 'index']);
$router->get('/beheer/api-sleutels/nieuw', [ApiSleutelController::class, 'aanmaken']);
$router->post('/beheer/api-sleutels', [ApiSleutelController::class, 'opslaan']);
$router->post('/beheer/api-sleutels/{id}/intrekken', [ApiSleutelController::class, 'intrekken']);
$router->post('/beheer/api-sleutels/{id}/heractiveren', [ApiSleutelController::class, 'heractiveren']);

$router->get('/beheer/log', [LogController::class, 'index']);
$router->get('/beheer/beveiliging', [BeveiligingController::class, 'index']);

$router->get('/beheer/exporteren', [ExportController::class, 'index']);
$router->post('/beheer/exporteren/uitvoeren', [ExportController::class, 'export']);

$router->get('/beheer/emails', [EmailQueueController::class, 'index']);
$router->post('/beheer/emails/test', [EmailQueueController::class, 'test']);

$router->get('/privacybeleid', [LegalController::class, 'privacybeleid']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
