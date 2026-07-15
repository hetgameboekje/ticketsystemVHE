<?php
// Eenmalig uitvoeren: php database/seed_locaties.php
// Maakt de hoofdlocatie aan (zichtbaar voor iedereen, niet aan een gebruiker gebonden).
// Gebruikt geen <seed> in locaties.xml: locaties.naam is bewust niet uniek (gebruikers mogen
// zelf locaties met dezelfde naam aanmaken), dus INSERT IGNORE zou niet idempotent zijn.
// Dit script checkt daarom eerst op bestaan voordat het aanmaakt, en kan dus veilig
// meerdere keren en op elke omgeving gedraaid worden.

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();

$locaties = [
    ['naam' => 'Hoofdlocatie', 'adres' => 'Luchthavenweg 10, Eindhoven', 'zichtbaarheid' => 'iedereen'],
];

$select = $pdo->prepare('SELECT id FROM locaties WHERE naam = ? AND aanmaker_id IS NULL AND deleted_at IS NULL');
$insert = $pdo->prepare('INSERT INTO locaties (naam, adres, zichtbaarheid, aanmaker_id) VALUES (?, ?, ?, NULL)');

foreach ($locaties as $locatie) {
    $select->execute([$locatie['naam']]);
    if ($select->fetch() !== false) {
        echo "Bestaat al, overgeslagen: {$locatie['naam']}\n";
        continue;
    }

    $insert->execute([$locatie['naam'], $locatie['adres'], $locatie['zichtbaarheid']]);
    echo "Aangemaakt: {$locatie['naam']} (id {$pdo->lastInsertId()})\n";
}
