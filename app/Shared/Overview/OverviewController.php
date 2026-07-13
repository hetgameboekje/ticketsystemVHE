<?php

namespace App\Shared\Overview;

use App\Core\Controller;

class OverviewController extends Controller
{
    private const ICT_GROUPS = [
        'Service' => [
            'tickets' => ['titel' => 'Ticket systeem', 'omschrijving' => 'Meldingen registreren, opvolgen en afhandelen.', 'link' => '/tickets'],
            'verbeterpunten' => ['titel' => 'Verbeterpunten', 'omschrijving' => 'Ideeën en verbetervoorstellen indienen en beoordelen.', 'link' => '/verbeterpunten'],
            'reflecties' => ['titel' => 'Reflectie', 'omschrijving' => 'Periodieke reflectieverslagen bijhouden.', 'link' => '/reflecties'],
            'kennisbank' => ['titel' => 'Kennisbank', 'omschrijving' => "Handleidingen, FAQ's en quick-action scripts.", 'link' => '/kennisbank'],
        ],
        'Assets' => [
            'voorraad' => ['titel' => 'Voorraad', 'omschrijving' => 'Voorraaditems en aantallen beheren.', 'link' => '/voorraad'],
            'uitgiften' => ['titel' => 'Uitgifte', 'omschrijving' => 'Items toewijzen aan medewerkers en retour nemen.', 'link' => '/uitgiften'],
            'apparaten' => ['titel' => 'Apparaten', 'omschrijving' => 'Overzicht van apparaten en hun status.', 'link' => '/apparaten'],
            'printers' => ['titel' => 'Printers', 'omschrijving' => 'Overzicht van printers en hun locaties.', 'link' => '/printers'],
            'schijfgebruik' => ['titel' => 'Schijfgebruik', 'omschrijving' => 'Schijfruimte en gebruik per locatie inzien.', 'link' => '/schijfgebruik'],
        ],
        'Security' => [
            'cyberrisicos' => ['titel' => "Cyberrisico's", 'omschrijving' => 'Gemelde incidenten en risico\'s bijhouden.', 'link' => '/cyberrisicos'],
        ],
    ];

    private const CRM_TILES = [
        'medewerkers' => ['titel' => 'Medewerkers', 'omschrijving' => 'Medewerkersgegevens en afdelingen beheren.', 'link' => '/medewerkers'],
    ];

    public function ict(): void
    {
        $this->requireAuth();

        $groups = [];
        foreach (self::ICT_GROUPS as $label => $tiles) {
            $toegestaan = array_filter($tiles, fn (string $module) => $this->hasRecht($module), ARRAY_FILTER_USE_KEY);
            if ($toegestaan) {
                $groups[$label] = $toegestaan;
            }
        }

        $this->render('Shared/Overview/Views/OverviewView/index', [
            'activeModule' => 'ict',
            'pageTitle' => 'ICT',
            'titel' => 'ICT',
            'groups' => $groups,
        ]);
    }

    public function crm(): void
    {
        $this->requireAuth();
        $this->renderOverzicht('CRM', self::CRM_TILES);
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
