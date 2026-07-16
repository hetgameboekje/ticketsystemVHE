# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

VHE Intranet Ticketsysteem — a modular intranet in plain PHP (no framework), MySQL/MariaDB. Core domain is ticket management, plus supporting modules (kennisbank, voorraad, agenda, CRM, etc.). Documentation, code comments, routes and UI text are in Dutch; keep new user-facing text and route segments in Dutch for consistency.

There is no composer.json, no autoloaded third-party dependencies, and no automated test suite — verification is done by running the app locally and exercising the affected flow.

## Commands

Run the app locally (no Apache/Laragon required):
```bash
php -S localhost:8000 -t public public/router.php
```

Database:
```bash
php database/parse.php     # regenerate database/.parsed/schema.sql from database/xml/*.xml
php database/seed.php      # seed demo users (admin@intranet.local / wachtwoord123)
php database/clear.php --force   # drop all local tables and rebuild schema
```
Table definitions live as XML in `database/xml/*.xml`; edit those, not `database/schema.sql` directly, then run `php database/parse.php`. The Beheer UI ("Database toepassen") can also add missing tables/columns automatically but never alters an existing column type.

Windows dev helper (interactive menu for the above plus git pull, `.env` rebuild, pulling a live DB dump):
```powershell
powershell -ExecutionPolicy Bypass -File scripts\dev-tools\dev-tools.ps1
```

No build step, linter, or test runner is configured — there's nothing to run beyond starting the PHP server and using the app/browser to verify changes.

## Architecture

Front controller is `public/index.php`, which requires `app/bootstrap.php` (PSR-4-ish autoloader under `App\`, `.env` loading, session start, timezone) and then `public/index.php` itself registers every route on a custom `Router` (`app/Core/Router.php`) before calling `dispatch()`. There is no separate routes file beyond `public/index.php` — new endpoints are added there.

- `app/Core` — shared infrastructure: `Router` (regex-based, `{id}` → numeric param), `Model` (static active-record-style base class: `all/find/create/update/delete/restore`, supports `$fillable` and soft deletes via `$softDeletes`/`deleted_at`), `Database` (lazy singleton PDO, sets MySQL session timezone to match `Europe/Amsterdam`), `DevSync` (dev-only auto git-pull + schema sync on `/login`), `Table`/`TableQuery` (list/table rendering helpers), `Mailer`, `Barcode`, `Xlsx`.
- `app/Shared` — cross-cutting concerns reused by multiple modules: `Auth` (session-based login), `User`/`Rechten` (permissions), `ApiKey` (scoped API keys for external scripts, e.g. email intake), `Crypto\FieldEncryptor` (field-level encryption for sensitive ticket columns), `Mail\EmailQueueProcessor` (queued outbound mail), `Log\PaginaBezoekLogger` (every dispatched request is logged), `Automation` (email intake / reminder cron endpoints), `Dashboard`, `Overview`, `Legal`.
- `app/Modules/<Name>` — one folder per business module (Ticket, Verbeterpunt, Reflectie, Kennisbank, HardwareUitgave, Medewerker, Voorraad, Device, Printer, CyberRisico, Uitgifte, Agenda, Account, Beheer, Tools, Script, Schijfgebruik). Each module has its own `*Controller.php`, `Models/*Model.php` (extends `App\Core\Model`), and `Views/<Name>View/*.php`. Most modules with loggable activity have a paired `*LogController` + log model (ticket_logs, verbeterpunt_logs, etc.) following the same pattern — copy an existing module's shape (e.g. Reflectie or CyberRisico) when adding a new one.
- `app/Views/layouts` — shared page layouts included by module views.

Routing convention: most modules are wired via the `$modules` array in `public/index.php`, which mechanically registers the standard `index/create/store/show/edit/update/destroy` routes (`GET /X`, `GET /X/create`, `POST /X`, `GET /X/{id}`, `GET /X/{id}/edit`, `POST /X/{id}`, `POST /X/{id}/verwijderen`) for each controller. Module-specific extra routes (log entries, exports, nested actions) are added individually below that loop.

### Config and environments

`config/config.php` reads from `getenv()` with Laragon-friendly local defaults; `.env` (gitignored, copy from `.env.example`) overrides per environment. Key flags:
- `APP_DEV` — when true, hitting `/login` triggers `DevSync`: automatic `git pull` + database parse/apply. Must be `false` in production.
- `APP_GIT_PULL_ENABLED` — separately gates the `exec('git pull')` call, since shared hosts (e.g. Hostnet) often disable `exec()`/have no shell access; database parsing still works with this off.
- `APP_ENCRYPTION_KEY` — base64 32-byte key for `App\Shared\Crypto\FieldEncryptor`, used to encrypt sensitive ticket fields (`omschrijving`, `opdrachtgever_naam`). Must be identical across all environments sharing a database — rotating it makes existing encrypted tickets unreadable. Generate with `openssl rand -base64 32`.
- `APP_URL` — base URL used to build absolute links in emails (e.g. reminder emails), since there's no active HTTP request context there.

Deployment target is Hostnet shared hosting (no SSH): `APP_DEV=false`, `APP_GIT_PULL_ENABLED=false`, deploy via SFTP, schema applied through phpMyAdmin, `public/uploads/` must be writable.

## Roadmap / openstaande verbeterpunten

Backlog van kleinere verbeterpunten, verdeeld in fases op basis van omvang en afhankelijkheden. Fases zijn een volgorde-advies, geen harde deadlines.

### Fase 1 — Logging (opruimen & hardening)
Kleine, onafhankelijke aanpassingen aan `App\Shared\Log\PaginaBezoekLogger` en de bijbehorende Beheer-view.
- Verdachte login pogingen mailen (bv. X mislukte pogingen binnen Y minuten → mail naar admin).
- Filter "niet ingelogd" toevoegen naast het bestaande filter op gebruiker.
- POST-data in een popup/modal tonen in plaats van inline in de tabelrij (scheelt ruimte in het overzicht).
- Opschoontaak/cron voor logregels ouder dan X dagen (config-waarde voor X).

### Fase 2 — Ticket & Kennisbank UX
Ticket en Kennisbank raken dezelfde patronen (omschrijving, categorie-select) — samen oppakken zodat de UI-componenten herbruikbaar zijn.
- Omschrijving uitklapbaar maken (collapsed/expand) in plaats van altijd volledig tonen — zowel bij Ticket als Kennisbank.
- Categorie aanpassen via zoekend/async selecteren: AJAX-lookup na ~2 seconden geen toetsaanslag, i.p.v. statische dropdown. Herbruikbare component voor Ticket én Kennisbank.
- Tijdregistratie op ticket in vaste blokken (5/10/15/30/45/60 min).
- Titel-veld toevoegen aan opmerkingen/reacties op een ticket.
- Ticket markeren als "Cyber risico" zodat hij meetelt in de CyberRisico-grafiek/module.

### Fase 3 — Uitgifte & Schijfgebruik (kleine features)
Losstaande, kleine toevoegingen aan bestaande modules.
- Uitgifte: vinkje "toestemming manager" toevoegen aan het uitgifteproces/-formulier.
- Schijfgebruik: device kunnen koppelen aan een medewerker.

### Fase 4 — Verbeterpunten/Ticket functionele gelijktrekking
Nadat Fase 2 (ticket UX) is opgeleverd: dezelfde functionaliteit (omschrijving uitklapbaar, categorie zoekend selecteren, tijdregistratie, opmerking-titel e.d.) toepassen op de Verbeterpunt-module, zodat beide modules qua functionaliteit gelijk lopen.

### Fase 5 — CRM: hiërarchie & urenstaat (grotere uitbreiding)
Grootste stuk nieuw werk, functioneel op te splitsen in twee delen:
- **Hiërarchie/stamboom**: keyusers en organisatiestructuur van VHE inzichtelijk maken binnen CRM (boomstructuur/hiërarchie-view).
- **Nieuwe extensie "Urenstaat"**: tijd registreren (tijdstip, locatie) gekoppeld aan CRM.
  - Locaties worden beheerd via een aparte, persoonsgebonden of algemene extensie: een locatie kan zichtbaar zijn voor iedereen (bv. "kantoor"), alleen de aanmaker (bv. "thuis"), of een selectieve groep gebruikers (bv. klant "Raith" zichtbaar voor 3 specifieke personen). Dit vraagt een zichtbaarheids-/rechtenmodel per locatie (vergelijkbaar met bestaande `Rechten`-aanpak), los van de generieke module-rechten.
  - Coördinaten per adres: voorlopig **handmatig invullen** naast het adresveld. Losse verkenning voor een geocoding-API blijft open (zie hieronder) — geen harde afhankelijkheid voor oplevering van de urenstaat-extensie zelf.

### Losse verkenning — geocoding/routing API (nog geen fase toegewezen)
Nog te onderzoeken, niet gekoppeld aan een deadline; input voor een eventuele latere fase (bv. reistijd-indicatie bij Urenstaat/locaties):
- OpenCage Geocoding API is getest voor adres → coördinaten (`api.opencagedata.com/geocode/v1/json`); vereist eigen API-key, rate limits nog niet uitgezocht.
- ANWB routing-API (`api.anwb.nl/routing/route/v1/route/car`) is getest voor reistijd/afstand tussen coördinaten (incl. tol-wegen); ongedocumenteerde publieke header (`x-anwb-caller-id`), gebruik hiervan in productie nader afwegen (stabiliteit/voorwaarden niet bevestigd).
- Voor nu: coördinaten handmatig invullen (zie Fase 5); geen API-integratie inbouwen totdat hier bewust voor gekozen wordt.






1. Enforce a Strict "No Ticket, No Help" PolicyStop Walk-ups: Politely but firmly tell users you cannot troubleshoot without a ticket.Guided Creation: Sit with them to submit the ticket while they are at your desk. This teaches them the submission process without shaming them.Standardized Intake: Use ticketing platforms like EasyDesk to ensure forms collect all necessary info immediately.2. Promote the "Path of Least Resistance"Centralize Resources: Make the IT portal the easiest place to get help, rather than relying on direct emails.Instant Context: Add a knowledge base right at the ticket submission page so users can self-solve before clicking "Submit".Proactive Communication: Send brief updates on common issue resolutions so users know self-service options are actually working.3. Implement Automation and DeflectionAutomate the Basics: Utilize endpoint management tools to automate software deployment and patching.Password Resets: Deploy self-service password reset software (SSPR) with multi-factor authentication so users can unlock accounts without IT intervention.Visual Guides: Place visual troubleshooting guides near high-traffic equipment (e.g., printers, conference room setups) to immediately cut recurring questions.