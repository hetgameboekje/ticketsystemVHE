<?php
// Eenmalig migratiescript: versleutelt tickets.omschrijving + tickets.opdrachtgever_naam voor
// bestaande rijen die dat nog niet zijn (plaintext van vóór de encryptie-migratie).
// Draai dit NA het toepassen van de kolomwijziging (opdrachtgever_naam -> TEXT) en NA het zetten
// van APP_ENCRYPTION_KEY in .env, en VOORDAT de nieuwe (versleutelende) code live gaat.
//
// Gebruik: php database/encrypt_existing_tickets.php          (proefdraai, toont alleen wat er zou gebeuren)
//          php database/encrypt_existing_tickets.php --apply  (versleutelt echt)

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Shared\Crypto\FieldEncryptor;

$apply = in_array('--apply', $argv, true);

/** Ruwe waarde lijkt al ons versleutelingsformaat (base64 van minimaal iv+tag = 28 bytes). */
function lijktAlVersleuteld(?string $waarde): bool
{
    if ($waarde === null || $waarde === '') {
        return true; // niets te versleutelen
    }
    $raw = base64_decode($waarde, true);
    return $raw !== false && strlen($raw) >= 28 && base64_encode($raw) === $waarde;
}

$rows = Database::pdo()->query('SELECT id, omschrijving, opdrachtgever_naam FROM tickets')->fetchAll();

$teVersleutelen = array_filter(
    $rows,
    fn (array $r) => !lijktAlVersleuteld($r['omschrijving']) || !lijktAlVersleuteld($r['opdrachtgever_naam'])
);

if (empty($teVersleutelen)) {
    echo "Geen tickets met nog-niet-versleutelde velden gevonden.\n";
    exit(0);
}

echo count($teVersleutelen) . " ticket(s) met plaintext omschrijving/opdrachtgever_naam gevonden.\n";

$stmt = Database::pdo()->prepare('UPDATE tickets SET omschrijving = ?, opdrachtgever_naam = ? WHERE id = ?');

foreach ($teVersleutelen as $row) {
    $action = $apply ? 'versleuteld:' : 'zou versleutelen:';
    echo "  {$action} ticket #{$row['id']}\n";

    if ($apply) {
        $stmt->execute([
            FieldEncryptor::encrypt($row['omschrijving']),
            FieldEncryptor::encrypt($row['opdrachtgever_naam']),
            $row['id'],
        ]);
    }
}

echo "\n";
if ($apply) {
    echo 'Klaar: ' . count($teVersleutelen) . " ticket(s) versleuteld.\n";
} else {
    echo "Proefdraai — er is niets aangepast. Voer uit met --apply om echt te versleutelen: php database/encrypt_existing_tickets.php --apply\n";
}
