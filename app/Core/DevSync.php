<?php

namespace App\Core;

/**
 * In dev-modus (config 'dev' => true) wordt bij het laden van /login automatisch
 * "git pull" gedaan en het databaseschema geparsed + toegepast, zodat een lokale
 * dev-omgeving nooit handmatig gesynchroniseerd hoeft te worden. In productie
 * (dev => false, bv. Hostnet) gebeurt dit bewust niet — daar kan het alleen nog
 * handmatig via de knoppen op de Beheer-pagina.
 */
class DevSync
{
    public static function isEnabled(): bool
    {
        $config = require APP_ROOT . '/config/config.php';
        return (bool) ($config['dev'] ?? false);
    }

    public static function isGitPullEnabled(): bool
    {
        $config = require APP_ROOT . '/config/config.php';
        return (bool) ($config['gitPullEnabled'] ?? false);
    }

    /** @return string[] logregels, alleen voor foutopsporing (wordt niet aan gebruikers getoond) */
    public static function run(): array
    {
        $log = [];

        if (self::isGitPullEnabled()) {
            $huidigeMap = getcwd();
            chdir(APP_ROOT);
            $output = [];
            $exitCode = 0;
            exec('git pull 2>&1', $output, $exitCode);
            chdir($huidigeMap);
            $log[] = 'git pull (' . ($exitCode === 0 ? 'OK' : 'FOUT') . '): ' . implode(' | ', $output);
        } else {
            $log[] = 'git pull overgeslagen (gitPullEnabled staat uit — geen shell-toegang op deze server).';
        }

        try {
            $sql = SchemaParser::generateSql();
            SchemaParser::writeSchemaFile($sql);
            $result = SchemaParser::applyToDatabase($sql);
            $log[] = "database parsen: {$result['applied']} statement(s) uitgevoerd, {$result['skipped']} overgeslagen";
        } catch (\Throwable $e) {
            $log[] = 'database parsen mislukt: ' . $e->getMessage();
        }

        return $log;
    }
}
