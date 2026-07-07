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

    // Sleutel voor het versleutelen van gevoelige ticketvelden (omschrijving, opdrachtgever_naam) —
    // zie App\Shared\Crypto\FieldEncryptor. Base64-encoded, 32 bytes. Genereren met:
    // openssl rand -base64 32
    // Zet 'm in .env (APP_ENCRYPTION_KEY=...), nooit hardcoded. Leeg = versleutelen/ontsleutelen
    // van tickets faalt met een duidelijke foutmelding.
    'encryptionKey' => getenv('APP_ENCRYPTION_KEY') ?: '',

    // Basis-URL van de applicatie, gebruikt om absolute links te bouwen in e-mails (bv. de
    // ticketlink in herinneringsmails) — daar is geen actieve HTTP-request/host bekend zoals
    // in een normale pageload. Zet 'm in .env (bv. https://intranet.vhe.nl of, lokaal,
    // http://ticketsysteemvhe.test), zonder trailing slash.
    'appUrl' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),

    'mail' => [
        'host' => getenv('MAIL_HOST') ?: '',
        'port' => (int) (getenv('MAIL_PORT') ?: 587),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@vhe.nl',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Ticketsysteem VHE',
        'admin_address' => getenv('MAIL_ADMIN_ADDRESS') ?: '',
    ],
];
