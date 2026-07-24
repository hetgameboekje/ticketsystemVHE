<?php
// Hernoemt de bestaande MySQL/MariaDB-database (de huidige DB_DATABASE uit .env/config) naar een
// nieuwe naam, zonder dataverlies. MySQL kent geen "RENAME DATABASE" meer (verwijderd sinds 5.1.23),
// dus dit script maakt de nieuwe database aan en verplaatst elke tabel ernaartoe met
// "RENAME TABLE oud.tabel TO nieuw.tabel" (server-side, geen dump/restore, seconden werk).
//
// Gebruik: php database/rename_database.php <nieuwe_naam> [--drop-old] [--from=<oude_naam>]
//   --drop-old        verwijdert de oude (na de move lege) database meteen na afloop.
//   --from=<oude_naam> gebruik deze bronnaam i.p.v. de huidige DB_DATABASE uit config/.env
//                      (handig als je .env niet (meer) overeenkomt met de echte databasenaam).
//
// Na afloop: zet DB_DATABASE=<nieuwe_naam> in je .env (zowel LOCAL_DB_DATABASE als evt.
// HOSTNET_DB_DATABASE als je die omgeving ook hernoemt) en herstart de PHP-server.

require __DIR__ . '/../app/bootstrap.php';

$args = array_slice($argv, 1);
$dropOld = false;
$oudeNaamOverride = null;
$nieuweNaam = null;
foreach ($args as $arg) {
    if ($arg === '--drop-old') {
        $dropOld = true;
        continue;
    }
    if (str_starts_with($arg, '--from=')) {
        $oudeNaamOverride = substr($arg, strlen('--from='));
        continue;
    }
    if ($nieuweNaam === null) {
        $nieuweNaam = $arg;
    }
}

if ($nieuweNaam === null || $nieuweNaam === '') {
    fwrite(STDERR, "Gebruik: php database/rename_database.php <nieuwe_naam> [--drop-old]\n");
    exit(1);
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $nieuweNaam)) {
    fwrite(STDERR, "Ongeldige databasenaam '{$nieuweNaam}': gebruik alleen letters, cijfers en underscores.\n");
    exit(1);
}

$config = require APP_ROOT . '/config/config.php';
$db = $config['db'];
$oudeNaam = $oudeNaamOverride ?? $db['database'];

if ($oudeNaam === $nieuweNaam) {
    fwrite(STDERR, "De database heet al '{$nieuweNaam}' — niets te doen.\n");
    exit(1);
}

// Verbinden zonder dbname te selecteren: we moeten tussen twee databases op dezelfde server
// kunnen schuiven, dat kan niet via App\Core\Database (die verbindt vast met één dbname).
$dsn = "mysql:host={$db['host']};port={$db['port']};charset=utf8mb4";
$pdo = new PDO($dsn, $db['username'], $db['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$bestaat = function (string $naam) use ($pdo): bool {
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$naam]);
    return $stmt->fetchColumn() !== false;
};

if (!$bestaat($oudeNaam)) {
    fwrite(STDERR, "Bron-database '{$oudeNaam}' bestaat niet (of niet bereikbaar met deze credentials).\n");
    exit(1);
}

if ($bestaat($nieuweNaam)) {
    fwrite(STDERR, "Doel-database '{$nieuweNaam}' bestaat al — kies een andere naam of verwijder 'm eerst.\n");
    exit(1);
}

$stmt = $pdo->prepare(
    'SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?'
);
$stmt->execute([$oudeNaam]);
$charset = $stmt->fetch(PDO::FETCH_ASSOC);
$charsetSql = $charset !== false
    ? " CHARACTER SET `{$charset['DEFAULT_CHARACTER_SET_NAME']}` COLLATE `{$charset['DEFAULT_COLLATION_NAME']}`"
    : '';

$pdo->exec("CREATE DATABASE `{$nieuweNaam}`{$charsetSql}");

$stmt = $pdo->prepare(
    "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'"
);
$stmt->execute([$oudeNaam]);
$tabellen = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tabellen as $tabel) {
    $veilig = str_replace('`', '``', $tabel);
    $pdo->exec("RENAME TABLE `{$oudeNaam}`.`{$veilig}` TO `{$nieuweNaam}`.`{$veilig}`");
    echo "  verplaatst: {$tabel}\n";
}

echo count($tabellen) . " tabel(len) verplaatst van '{$oudeNaam}' naar '{$nieuweNaam}'.\n";

if ($dropOld) {
    $pdo->exec("DROP DATABASE `{$oudeNaam}`");
    echo "Oude database '{$oudeNaam}' verwijderd.\n";
} else {
    echo "Oude database '{$oudeNaam}' is nu leeg maar nog niet verwijderd (draai met --drop-old om 'm meteen op te ruimen).\n";
}

echo "\nZet nu DB_DATABASE={$nieuweNaam} (LOCAL_DB_DATABASE in .env) en herstart de PHP-server.\n";
