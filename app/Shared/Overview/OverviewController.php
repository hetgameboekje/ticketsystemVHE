<?php

namespace App\Shared\Overview;

use App\Core\Controller;

class OverviewController extends Controller
{
    private const SERVICE_TILES = [
        'tickets' => ['titel' => 'Ticket systeem', 'omschrijving' => 'Meldingen registreren, opvolgen en afhandelen.', 'link' => '/tickets'],
        'verbeterpunten' => ['titel' => 'Verbeterpunten', 'omschrijving' => 'Ideeën en verbetervoorstellen indienen en beoordelen.', 'link' => '/verbeterpunten'],
        'reflecties' => ['titel' => 'Reflectie', 'omschrijving' => 'Periodieke reflectieverslagen bijhouden.', 'link' => '/reflecties'],
        'kennisbank' => ['titel' => 'Kennisbank', 'omschrijving' => "Handleidingen, FAQ's en quick-action scripts.", 'link' => '/kennisbank'],
    ];

    private const ASSETS_TILES = [
        'voorraad' => ['titel' => 'Voorraad', 'omschrijving' => 'Voorraaditems en aantallen beheren.', 'link' => '/voorraad'],
        'uitgiften' => ['titel' => 'Uitgifte', 'omschrijving' => 'Items toewijzen aan medewerkers en retour nemen.', 'link' => '/uitgiften'],
        'printers' => ['titel' => 'Printers', 'omschrijving' => 'Overzicht van printers en hun locaties.', 'link' => '/printers'],
    ];

    private const ONDERHOUD_TILES = [
        'schijfgebruik' => ['titel' => 'Schijfgebruik', 'omschrijving' => 'Schijfruimte en apparaatgezondheid monitoren.', 'link' => '/schijfgebruik'],
        'apparaten' => ['titel' => 'Applicaties', 'omschrijving' => 'Geïnstalleerde software per apparaat inzien.', 'link' => '/apparaten'],
        'scripts' => ['titel' => 'Scripts', 'omschrijving' => 'Herbruikbare quick-action scripts beheren.', 'link' => '/scripts'],
    ];

    private const SECURITY_TILES = [
        'cyberrisicos' => ['titel' => "Cyberrisico's", 'omschrijving' => 'Gemelde incidenten en risico\'s bijhouden.', 'link' => '/cyberrisicos'],
    ];

    private const CRM_TILES = [
        'medewerkers' => ['titel' => 'Medewerkers', 'omschrijving' => 'Medewerkersgegevens en afdelingen beheren.', 'link' => '/medewerkers'],
    ];

    public function service(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('Service', self::SERVICE_TILES);
    }

    public function assets(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('Assets', self::ASSETS_TILES);
    }

    public function security(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('Security', self::SECURITY_TILES);
    }

    public function crm(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('CRM', self::CRM_TILES);
    }

    public function onderhoud(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('Onderhoud', self::ONDERHOUD_TILES);
    }

    private function renderOverzicht(string $titel, array $tiles): void
    {
        $toegestaan = array_filter($tiles, fn (string $module) => $this->hasRecht($module), ARRAY_FILTER_USE_KEY);

        $this->render('Shared/Overview/Views/OverviewView/index', [
            'activeModule' => strtolower($titel),
            'pageTitle' => $titel,
            'titel' => $titel,
            'tiles' => $toegestaan,
        ]);
    }
}
