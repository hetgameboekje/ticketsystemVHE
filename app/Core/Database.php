<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function pdo(): PDO
    {
        if (self::$instance === null) {
            $config = require APP_ROOT . '/config/config.php';
            $db = $config['db'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset=utf8mb4";

            self::$instance = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Zet de sessie-tijdzone naar het actuele Nederlandse UTC-offset (+01:00 's winters, +02:00
            // 's zomers) zodat CURRENT_TIMESTAMP/NOW() op de juiste lokale tijd staan. Een numerieke offset
            // i.p.v. 'Europe/Amsterdam' werkt ook op servers zonder geladen MySQL-tijdzonetabellen.
            $offset = (new \DateTime('now', new \DateTimeZone('Europe/Amsterdam')))->format('P');
            self::$instance->exec("SET time_zone = '{$offset}'");
        }

        return self::$instance;
    }
}
