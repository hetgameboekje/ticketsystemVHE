<?php

return [
    // true = lokale ontwikkelomgeving: /login pullt en parsed het schema automatisch (zie App\Core\DevSync).
    // false = productie (bv. Hostnet): alleen handmatig via Beheer > Git pull / Database parsen.
    // Zet dit hardcoded naar false voordat je naar Hostnet deployt, of override via .env (APP_DEV=false).
    'dev' => filter_var(getenv('APP_DEV') ?: 'true', FILTER_VALIDATE_BOOLEAN),

    // Schakelt alle server-side shell-uitvoering uit (git pull, en het git-onderdeel van
    // de dev-sync). Nodig op hosts zonder shell/exec-toegang, zoals Hostnet shared webhosting
    // (cPanel/DirectAdmin zonder SSH) — daar faalt exec() sowieso of staat 'm uit in php.ini.
    // Zet dit hardcoded naar false op shared hosting, of override via .env (APP_GIT_PULL_ENABLED=false).
    // Op een VPS/Docker-omgeving met shell-toegang kan dit gewoon true blijven.
    'gitPullEnabled' => filter_var(getenv('APP_GIT_PULL_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),

    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'vhe',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],

    // Gedeeld geheim voor POST /api/tickets/vanuit-email (bv. het Outlook-script op it@vhe.nl).
    // Leeg = endpoint wijst elk verzoek af. Zet via .env (TICKET_EMAIL_INTAKE_API_KEY=...), nooit hardcoded.
    'ticketEmailIntakeApiKey' => getenv('TICKET_EMAIL_INTAKE_API_KEY') ?: '',
];
