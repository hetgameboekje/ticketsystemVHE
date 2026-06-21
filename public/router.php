<?php
// Gebruikt door PHP's built-in server: php -S localhost:8000 -t public public/router.php
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($path !== '/' && file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
