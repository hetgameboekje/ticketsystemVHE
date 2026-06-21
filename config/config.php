<?php

return [
    'db' => [
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'port'     => getenv('DB_PORT') ?: '5432',
        'database' => getenv('DB_DATABASE') ?: 'intranet',
        'username' => getenv('DB_USERNAME') ?: 'postgres',
        'password' => getenv('DB_PASSWORD') ?: 'postgres',
    ],
];
