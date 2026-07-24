<?php

// APP_ENV kiest welk omgevingsprofiel hieronder geldt: 'local' (default) of 'hostnet'.
// .env is verder identiek op je eigen machine en op de live Hostnet-server (bevat beide
// LOCAL_*/HOSTNET_*-blokken) — alleen deze ene regel zet je bewust handmatig anders per plek.
$appEnv = strtolower(trim((string) (getenv('APP_ENV') ?: 'local'))) === 'hostnet' ? 'hostnet' : 'local';
$prefix = $appEnv === 'hostnet' ? 'HOSTNET' : 'LOCAL';

// Leest "{LOCAL,HOSTNET}_{key}" met een default — scheelt de prefix overal apart uitschrijven.
$env = static fn (string $key, string $default = ''): string => getenv("{$prefix}_{$key}") ?: $default;

return [
    'env' => $appEnv,

    // true = lokale ontwikkelomgeving: /login pullt en parsed het schema automatisch (zie App\Core\DevSync).
    // false = productie (Hostnet): alleen handmatig via Beheer > Git pull / Database parsen.
    // Volledig afgeleid van APP_ENV — hostnet heeft geen shell-toegang (bv. Hostnet shared
    // webhosting zonder SSH, waar exec() sowieso faalt of uitstaat in php.ini) en mag niet
    // automatisch pullen/syncen bij elke /login.
    'dev' => $appEnv !== 'hostnet',
    'gitPullEnabled' => $appEnv !== 'hostnet',

    'db' => [
        'host'     => $env('DB_HOST', '127.0.0.1'),
        'port'     => $env('DB_PORT', '3306'),
        'database' => $env('DB_DATABASE', 'leenvanpunt'),
        'username' => $env('DB_USERNAME', 'root'),
        'password' => $env('DB_PASSWORD', ''),
    ],

    // Sleutel voor het versleutelen van gevoelige ticketvelden (omschrijving, opdrachtgever_naam) —
    // zie App\Shared\Crypto\FieldEncryptor. Base64-encoded, 32 bytes. Genereren met:
    // openssl rand -base64 32
    // Bewust NIET per omgeving geprefixt: moet identiek blijven zolang omgevingen dezelfde
    // (of een kopie van dezelfde) database kunnen delen — rotatie maakt bestaande versleutelde
    // tickets onleesbaar. Zet 'm in .env (APP_ENCRYPTION_KEY=...), nooit hardcoded.
    'encryptionKey' => getenv('APP_ENCRYPTION_KEY') ?: '',

    // Basis-URL van de applicatie, gebruikt om absolute links te bouwen in e-mails (bv. de
    // ticketlink in herinneringsmails), omdat daar geen actieve HTTP-request bekend is.
    'appUrl' => rtrim($env('APP_URL', 'http://localhost'), '/'),

    // Aantal dagen dat logregels (paginabezoeken, login-pogingen) bewaard blijven voordat ze
    // opgeruimd worden door /api/logs/opschonen (zie App\Shared\Automation\AutomationController).
    // Niet per omgeving geprefixt — hoeft niet te verschillen tussen local/hostnet.
    'logRetentieDagen' => (int) (getenv('LOG_RETENTIE_DAGEN') ?: 90),

    // AI-verwerking voor de e-mail-/kennisbankverwerking (App\Modules\EmailVerwerking\Services\AiAnalysisService)
    // draait via een eigen n8n-orkestratielaag i.p.v. een directe AI-provider-call.
    // Niet per omgeving geprefixt: dezelfde webhook/sleutel gelden lokaal en op Hostnet.
    'ai' => [
        'confidenceDrempel' => (float) (getenv('AI_CONFIDENCE_DREMPEL') ?: 0.75),
    ],

    'n8n' => [
        'webhookUrl' => getenv('N8N_WEBHOOK_URL') ?: '',
        'apiKey' => getenv('N8N_API_KEY') ?: '',
    ],

    'mail' => [
        'host' => $env('MAIL_HOST'),
        'port' => (int) $env('MAIL_PORT', '587'),
        'encryption' => $env('MAIL_ENCRYPTION', 'tls'),
        'username' => $env('MAIL_USERNAME'),
        'password' => $env('MAIL_PASSWORD'),
        'from_address' => $env('MAIL_FROM_ADDRESS', 'noreply@bergthaler.dev'),
        'from_name' => $env('MAIL_FROM_NAME', 'Ticketsysteem Leen van Punt'),
        'admin_address' => $env('MAIL_ADMIN_ADDRESS'),
    ],
];
