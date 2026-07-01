<?php
// Genereert database/.parsed/schema.sql uit de tabel-definities in database/xml/*.xml.
// Gebruik: php database/parse.php
// (Zelfde logica als de "Database parsen"-knop in Beheer > app/Core/SchemaParser.php)

require __DIR__ . '/../app/bootstrap.php';

use App\Core\SchemaParser;

$sql = SchemaParser::generateSql();
$path = SchemaParser::writeSchemaFile($sql);

echo 'Geschreven naar ' . $path . PHP_EOL;
