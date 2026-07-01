<?php

define('APP_ROOT', dirname(__DIR__));

// .env is optioneel (staat in .gitignore) — handig om per omgeving (bv. Hostnet-productie)
// eigen DB_* / dev-instellingen te zetten zonder config/config.php aan te passen.
$envFile = APP_ROOT . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
        }
    }
}
unset($envFile);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
