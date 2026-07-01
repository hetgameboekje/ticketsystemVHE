<?php

namespace App\Core;

/**
 * Zet database/xml/*.xml om naar SQL (CREATE TABLE IF NOT EXISTS + INSERT IGNORE-seeds)
 * en kan die SQL optioneel meteen tegen de live database uitvoeren. Omdat alle statements
 * idempotent zijn, worden bestaande tabellen/rijen nooit aangepast — alleen wat nog
 * ontbreekt wordt toegevoegd.
 */
class SchemaParser
{
    public static function generateSql(): string
    {
        $xmlDir = APP_ROOT . '/database/xml';

        $tables = [];
        foreach (glob($xmlDir . '/*.xml') as $file) {
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                throw new \RuntimeException("Kan {$file} niet parsen als XML.");
            }
            $tables[(string) $xml['name']] = $xml;
        }

        $ordered = [];
        $visited = [];
        foreach (array_keys($tables) as $name) {
            self::visitTable($name, $tables, $ordered, $visited);
        }

        $sql = "-- Gegenereerd door database/parse.php — niet handmatig bewerken.\n";
        $sql .= "-- Bron: database/xml/*.xml\n\n";

        foreach ($ordered as $name) {
            $sql .= self::buildCreateTable($tables[$name]) . "\n\n";
        }

        foreach ($ordered as $name) {
            $seedSql = self::buildSeed($tables[$name]);
            if ($seedSql !== '') {
                $sql .= $seedSql . "\n\n";
            }
        }

        return $sql;
    }

    public static function writeSchemaFile(string $sql): string
    {
        $outDir = APP_ROOT . '/database/.parsed';
        if (!is_dir($outDir)) {
            mkdir($outDir, 0777, true);
        }

        $path = $outDir . '/schema.sql';
        file_put_contents($path, $sql);

        return $path;
    }

    /**
     * Voert de gegenereerde SQL statement-voor-statement uit tegen de live database.
     * CREATE TABLE IF NOT EXISTS en INSERT IGNORE zijn idempotent: bestaande tabellen/rijen
     * blijven ongewijzigd, alleen ontbrekende tabellen/seed-rijen worden toegevoegd.
     * Wordt alleen aangeroepen vanuit dev-modus (App\Core\DevSync) — de Beheer-knop
     * "Database parsen" genereert het bestand alleen en voert het bewust niet uit.
     *
     * @return array{applied: int, skipped: int, errors: string[]}
     */
    public static function applyToDatabase(string $sql): array
    {
        $pdo = Database::pdo();
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $applied = 0;
        $skipped = 0;
        $errors = [];

        foreach ($statements as $statement) {
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            try {
                $pdo->exec($statement);
                $applied++;
            } catch (\PDOException $e) {
                $skipped++;
                $errors[] = $e->getMessage();
            }
        }

        return ['applied' => $applied, 'skipped' => $skipped, 'errors' => $errors];
    }

    private static function tableDependencies(\SimpleXMLElement $table): array
    {
        $deps = [];
        foreach ($table->columns->column as $column) {
            $ref = (string) $column['references'];
            if ($ref !== '') {
                $deps[] = explode('.', $ref)[0];
            }
        }
        return $deps;
    }

    private static function visitTable(string $name, array $tables, array &$ordered, array &$visited): void
    {
        if (isset($visited[$name]) || !isset($tables[$name])) {
            return;
        }
        $visited[$name] = true;
        foreach (self::tableDependencies($tables[$name]) as $dep) {
            if ($dep !== $name) {
                self::visitTable($dep, $tables, $ordered, $visited);
            }
        }
        $ordered[] = $name;
    }

    private static function buildCreateTable(\SimpleXMLElement $table): string
    {
        $name = (string) $table['name'];
        $engine = (string) ($table['engine'] ?: 'InnoDB');

        $lines = [];
        $primary = null;
        $foreignKeys = [];

        foreach ($table->columns->column as $column) {
            $colName = (string) $column['name'];
            $type = (string) $column['type'];
            $length = (string) $column['length'];

            $line = "    {$colName} {$type}" . ($length !== '' ? "({$length})" : '');

            if ((string) $column['auto_increment'] === 'true') {
                $line .= ' AUTO_INCREMENT';
            }
            if ((string) $column['nullable'] === 'false') {
                $line .= ' NOT NULL';
            }
            if ((string) $column['unique'] === 'true') {
                $line .= ' UNIQUE';
            }
            $default = (string) $column['default'];
            if ($default !== '') {
                $line .= " DEFAULT {$default}";
            }
            $onUpdate = (string) $column['on_update'];
            if ($onUpdate !== '') {
                $line .= " ON UPDATE {$onUpdate}";
            }

            $lines[] = $line;

            if ((string) $column['primary'] === 'true') {
                $primary = $colName;
            }

            $ref = (string) $column['references'];
            if ($ref !== '') {
                [$refTable, $refCol] = explode('.', $ref);
                $fk = "    FOREIGN KEY ({$colName}) REFERENCES {$refTable}({$refCol})";
                $onDelete = (string) $column['on_delete'];
                if ($onDelete !== '') {
                    $fk .= " ON DELETE {$onDelete}";
                }
                $foreignKeys[] = $fk;
            }
        }

        if ($primary !== null) {
            $lines[] = "    PRIMARY KEY ({$primary})";
        }
        $lines = array_merge($lines, $foreignKeys);

        return "CREATE TABLE IF NOT EXISTS {$name} (\n" . implode(",\n", $lines) . "\n) ENGINE={$engine};";
    }

    private static function buildSeed(\SimpleXMLElement $table): string
    {
        if (!isset($table->seed) || !isset($table->seed->row)) {
            return '';
        }

        $name = (string) $table['name'];
        $statements = [];

        foreach ($table->seed->row as $row) {
            $columns = [];
            $values = [];
            foreach ($row->value as $value) {
                $columns[] = (string) $value['column'];
                $values[] = "'" . addslashes((string) $value) . "'";
            }
            $statements[] = "INSERT IGNORE INTO {$name} (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
        }

        return implode("\n", $statements);
    }
}
