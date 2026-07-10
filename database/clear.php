<?php
// Verwijdert ALLE tabellen (en dus alle data) uit de database en herbouwt het schema vanuit
// database/xml/*.xml. Vereist --force om te voorkomen dat dit per ongeluk gedraaid wordt.
// Gebruik: php database/clear.php --force

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Core\SchemaParser;

if (!in_array('--force', $argv, true)) {
    fwrite(STDERR, "Dit verwijdert ALLE tabellen en data. Voer uit met --force om te bevestigen.\n");
    exit(1);
}

$pdo = Database::pdo();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$sql = SchemaParser::generateSql();
SchemaParser::writeSchemaFile($sql);
$result = SchemaParser::applyToDatabase($sql);

echo "Database geleegd en schema herbouwd: {$result['applied']} statement(s) uitgevoerd." . PHP_EOL;
if ($result['errors'] !== []) {
    echo "Foutmeldingen:\n- " . implode("\n- ", $result['errors']) . PHP_EOL;
}
