<?php

namespace App\Core;

/**
 * Zet database/xml/*.xml om naar SQL (CREATE TABLE IF NOT EXISTS + INSERT IGNORE-seeds)
 * en kan die SQL, plus eventuele ontbrekende kolommen op reeds bestaande tabellen,
 * tegen de live database uitvoeren. Alles is idempotent: bestaande tabellen/kolommen/rijen
 * worden nooit aangepast — alleen wat nog ontbreekt wordt toegevoegd.
 */
class SchemaParser
{
    /** @return array<string, \SimpleXMLElement> tabelnaam => geparste XML, in geen specifieke volgorde */
    private static function loadTables(): array
    {
        $tables = [];
        foreach (glob(APP_ROOT . '/database/xml/*.xml') as $file) {
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                throw new \RuntimeException("Kan {$file} niet parsen als XML.");
            }
            $tables[(string) $xml['name']] = $xml;
        }

        return $tables;
    }

    /** @return string[] tabelnamen in dependency-volgorde (referenties vóór de tabellen die ernaar verwijzen) */
    private static function orderedTableNames(array $tables): array
    {
        $ordered = [];
        $visited = [];
        foreach (array_keys($tables) as $name) {
            self::visitTable($name, $tables, $ordered, $visited);
        }

        return $ordered;
    }

    public static function generateSql(): string
    {
        $tables = self::loadTables();
        $ordered = self::orderedTableNames($tables);

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
     * Voert de gegenereerde SQL statement-voor-statement uit tegen de live database, en
     * voegt daarna ontbrekende kolommen toe aan tabellen die al bestaan maar achterlopen
     * op database/xml/*.xml (bv. na een eerdere handmatige/deel-toepassing van het schema).
     * CREATE TABLE IF NOT EXISTS, INSERT IGNORE en de kolom-aanvulling zijn alle drie
     * idempotent: bestaande tabellen/kolommen/rijen blijven ongewijzigd.
     * Wordt alleen aangeroepen vanuit dev-modus (App\Core\DevSync) — de Beheer-knop
     * "Database parsen" genereert het bestand alleen en voert het bewust niet uit.
     *
     * @return array{applied: int, skipped: int, errors: string[]}
     */
    public static function applyToDatabase(string $sql): array
    {
        $pdo = Database::pdo();
        // Regelcommentaar eerst verwijderen: anders smelt het "-- Gegenereerd door..."-kopblok samen
        // met de eerste CREATE TABLE-statement na explode(';'), waardoor die ene statement met "--"
        // begint en als comment wordt overgeslagen (de eerste tabel in schema.sql zou zo nooit
        // aangemaakt worden).
        $sqlZonderCommentaar = preg_replace('/^--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sqlZonderCommentaar)));

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

        $columnResult = self::applyMissingColumns($pdo);
        $applied += $columnResult['applied'];
        $skipped += $columnResult['skipped'];
        $errors = array_merge($errors, $columnResult['errors']);

        return ['applied' => $applied, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * Vergelijkt elke tabel in database/xml/*.xml met de daadwerkelijke kolommen op de live
     * database, en voegt via ALTER TABLE ... ADD COLUMN toe wat in de XML staat maar nog
     * ontbreekt in de database. Tabellen die nog helemaal niet bestaan worden overgeslagen
     * (die krijgen hun kolommen al compleet via CREATE TABLE IF NOT EXISTS).
     *
     * @return array{applied: int, skipped: int, errors: string[]}
     */
    private static function applyMissingColumns(\PDO $pdo): array
    {
        $applied = 0;
        $skipped = 0;
        $errors = [];

        foreach (self::loadTables() as $name => $table) {
            $existsStmt = $pdo->prepare('SHOW TABLES LIKE ?');
            $existsStmt->execute([$name]);
            if ($existsStmt->fetchColumn() === false) {
                continue;
            }

            $columnsStmt = $pdo->query("SHOW COLUMNS FROM `{$name}`");
            $existingColumns = array_column($columnsStmt->fetchAll(\PDO::FETCH_ASSOC), 'Field');

            foreach ($table->columns->column as $column) {
                $colName = (string) $column['name'];
                if (in_array($colName, $existingColumns, true)) {
                    continue;
                }

                $statement = "ALTER TABLE `{$name}` ADD COLUMN " . self::buildColumnDefinition($column);

                try {
                    $pdo->exec($statement);
                    $applied++;
                } catch (\PDOException $e) {
                    $skipped++;
                    $errors[] = $e->getMessage();
                }
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

    /**
     * Alle id/primary-key-kolommen in de live database zijn BIGINT UNSIGNED (zo aangemaakt vóór
     * database/xml/*.xml bestond) — XML zelf zegt overal "INT". MySQL staat een FOREIGN KEY alleen
     * toe als het type van de refererende kolom exact overeenkomt met het type van de referenced
     * kolom, dus elke "INT"-kolom die een primary key is of een "references" heeft, moet als
     * BIGINT UNSIGNED gerenderd worden — anders faalt CREATE TABLE voor elke tabel die nog niet
     * bestaat en naar een andere tabel verwijst (zie bv. login_attempts, devices, api_keys).
     */
    private static function resolveType(\SimpleXMLElement $column): string
    {
        $type = (string) $column['type'];
        $isIdColumn = (string) $column['primary'] === 'true' || (string) $column['references'] !== '';

        return $type === 'INT' && $isIdColumn ? 'BIGINT UNSIGNED' : $type;
    }

    /** Bouwt de "kolomnaam TYPE(lengte) [modifiers]"-fragment, gebruikt door zowel CREATE TABLE als ALTER TABLE. */
    private static function buildColumnDefinition(\SimpleXMLElement $column): string
    {
        $colName = (string) $column['name'];
        $type = self::resolveType($column);
        $length = (string) $column['length'];

        $line = "{$colName} {$type}" . ($length !== '' ? "({$length})" : '');

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
        } elseif ($type === 'TIMESTAMP' && (string) $column['nullable'] !== 'false') {
            // MySQL geeft een TIMESTAMP-kolom zonder expliciete default anders impliciet
            // NOT NULL DEFAULT '0000-00-00 00:00:00', wat onder strict mode een
            // "Invalid default value"-fout oplevert. Expliciet NULL voorkomt dat.
            $line .= ' NULL DEFAULT NULL';
        }
        $onUpdate = (string) $column['on_update'];
        if ($onUpdate !== '') {
            $line .= " ON UPDATE {$onUpdate}";
        }

        return $line;
    }

    private static function buildCreateTable(\SimpleXMLElement $table): string
    {
        $name = (string) $table['name'];
        $engine = (string) ($table['engine'] ?: 'InnoDB');

        $lines = [];
        $primary = null;
        $foreignKeys = [];

        foreach ($table->columns->column as $column) {
            $lines[] = '    ' . self::buildColumnDefinition($column);

            if ((string) $column['primary'] === 'true') {
                $primary = (string) $column['name'];
            }

            $ref = (string) $column['references'];
            if ($ref !== '') {
                [$refTable, $refCol] = explode('.', $ref);
                $fk = "    FOREIGN KEY (" . (string) $column['name'] . ") REFERENCES {$refTable}({$refCol})";
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
