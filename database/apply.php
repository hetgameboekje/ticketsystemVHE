<?php
// Genereert het schema en past het direct toe op de database (CREATE TABLE IF NOT EXISTS,
// ALTER TABLE ... ADD COLUMN, INSERT IGNORE-seeds). Idempotent.
// Gebruik: php database/apply.php
// (Zelfde logica als de "Toepassen op database"-knop in Beheer > app/Core/SchemaParser.php)

require __DIR__ . '/../app/bootstrap.php';

use App\Core\SchemaParser;

$sql = SchemaParser::generateSql();
SchemaParser::writeSchemaFile($sql);
$result = SchemaParser::applyToDatabase($sql);

echo "Toegepast: {$result['applied']} statement(s), overgeslagen: {$result['skipped']}." . PHP_EOL;
if ($result['errors'] !== []) {
    echo "Foutmeldingen:\n- " . implode("\n- ", $result['errors']) . PHP_EOL;
}
