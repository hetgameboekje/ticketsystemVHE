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

    private const AGENDA_TILES = [
        'agenda' => ['titel' => 'Agenda', 'omschrijving' => 'Afspraken inplannen en het teamoverzicht bekijken.', 'link' => '/agenda'],
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
        'urenstaat' => ['titel' => 'Urenstaat', 'omschrijving' => 'Gewerkte uren en locaties registreren.', 'link' => '/urenstaat'],
    ];

    private const TOOLS_TILES = [
        'telefoonlijst' => ['titel' => 'Telefoonlijst naar VCF', 'omschrijving' => 'Interne telefoonlijst omzetten naar een .vcf-bestand.', 'link' => '/tools/telefoonlijst'],
        'handtekeningen' => ['titel' => 'Handtekeningen', 'omschrijving' => 'E-mailhandtekening samenstellen uit tekst, iconen en logo\'s.', 'link' => '/tools/handtekeningen'],
        'herstart-herinneringen' => ['titel' => 'Herstart-herinneringen', 'omschrijving' => 'Apparaten die lang niet herstart zijn en herinneringsmails versturen.', 'link' => '/tools/herstart-herinneringen'],
        'installatie' => ['titel' => 'Installatie', 'omschrijving' => 'Installatie-checklist beheren, printen of digitaal afvinken.', 'link' => '/tools/installatie'],
    ];

    public function index(): void
    {
        $this->requireAuth();

        $categorieen = [
            'Service' => self::SERVICE_TILES,
            'Agenda' => self::AGENDA_TILES,
            'Assets' => self::ASSETS_TILES,
            'Security' => self::SECURITY_TILES,
            'Onderhoud' => self::ONDERHOUD_TILES,
            'CRM' => self::CRM_TILES,
        ];

        $groups = [];
        foreach ($categorieen as $label => $tiles) {
            $toegestaan = array_filter($tiles, fn (string $module) => $this->hasRecht($module), ARRAY_FILTER_USE_KEY);
            if ($toegestaan !== []) {
                $groups[$label] = $toegestaan;
            }
        }

        // Tools zijn niet aan de rechtenmatrix gekoppeld — elke ingelogde gebruiker mag ze gebruiken.
        $groups['Tools'] = self::TOOLS_TILES;

        $this->render('Shared/Overview/Views/OverviewView/index', [
            'activeModule' => 'overzicht',
            'pageTitle' => 'Overzicht',
            'titel' => 'Overzicht',
            'groups' => $groups,
        ]);
    }
}
