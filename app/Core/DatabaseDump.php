<?php

namespace App\Core;

use PDO;

/**
 * Genereert een volledige SQL-dump (schema + data, alle tabellen) van de live database via PDO —
 * er is bewust geen mysqldump nodig, want Hostnet shared hosting heeft geen shell-toegang.
 * Gebruikt door AutomationController::databaseExport() (scope 'database_export'), zodat een lokale
 * dev-omgeving de live database kan binnenhalen via scripts/dev-tools/dev-tools.ps1 ("Live database ophalen").
 */
class DatabaseDump
{
    public static function generate(): string
    {
        $pdo = Database::pdo();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- Volledige databasedump, gegenereerd " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\nSET NAMES utf8mb4;\n\n";

        foreach ($tables as $table) {
            $sql .= self::dumpTable($pdo, $table);
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        return $sql;
    }

    private static function dumpTable(PDO $pdo, string $table): string
    {
        $quoted = self::quoteIdentifier($table);

        $createRow = $pdo->query("SHOW CREATE TABLE {$quoted}")->fetch();
        $createSql = $createRow['Create Table'] ?? '';

        $out = "DROP TABLE IF EXISTS {$quoted};\n{$createSql};\n\n";

        $stmt = $pdo->query("SELECT * FROM {$quoted}");
        $columns = null;
        $valueLines = [];

        foreach ($stmt as $row) {
            $columns ??= array_keys($row);
            $values = array_map(
                fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                $row
            );
            $valueLines[] = '(' . implode(', ', $values) . ')';
        }

        if ($valueLines !== []) {
            $columnList = implode(', ', array_map([self::class, 'quoteIdentifier'], $columns));
            $out .= "INSERT INTO {$quoted} ({$columnList}) VALUES\n" . implode(",\n", $valueLines) . ";\n\n";
        }

        return $out;
    }

    private static function quoteIdentifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
}
