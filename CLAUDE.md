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

**Al opgeleverd** (gecontroleerd tegen de code op 2026-07-20, staat er niet meer als open werk):
Logging (verdachte-login mail, "niet ingelogd"-filter, POST-data popup, opschoon-cron), Uitgifte-manager-vinkje, Ticket UX (uitklapbare omschrijving, cyber-risico-vlag + grafiek, tijdregistratie in vaste blokken, opmerking-titel, zoekende categorie-select), Kennisbank zoekende categorie-select, Schijfgebruik device↔medewerker-koppeling, en Verbeterpunt-Ticket gelijktrekking (Verbeterpunt heeft al dezelfde uitklapbare omschrijving/titel/tijdregistratie/categorie-select als Ticket).

Kleine kanttekening: de categorie-zoekfunctie (Ticket/Kennisbank/Verbeterpunt) gebruikt nu een debounce van ~200ms in plaats van de gewenste ~2s — pas aan als dit als hinderlijk ervaren wordt (te veel requests tijdens typen).

**Fase 1–4 zijn gebouwd** (2026-07-20, nog niet lokaal getest — geen PHP CLI beschikbaar in de omgeving waarin dit gebouwd is; run `php database/parse.php` of gebruik Beheer → "Database toepassen" om de nieuwe kolommen/tabellen toe te passen, en loop de flows hieronder na in de browser):

### Fase 1 — CRM: hiërarchie/stamboom ✅
`medewerkers` heeft nu `manager_id` (self-referencing FK) en `is_keyuser` (tinyint). Nieuwe view `GET /medewerkers/hierarchie` toont een boomstructuur (`MedewerkerController::hierarchie()`, `MedewerkerModel::alleVoorHierarchie()`), keyusers krijgen een badge. Manager-select en keyuser-vinkje toegevoegd aan het medewerker create/edit-formulier.

### Fase 2 — Urenstaat koppelen aan CRM/klant ✅
`urenstaat_registraties` heeft een nieuwe nullable `keyuser_id` (FK → `medewerkers.id`), instelbaar via een "Keyuser/klant"-select in het urenstaat create/edit-formulier en zichtbaar in index/show (`MedewerkerModel::alleKeyusers()`).

### Fase 3 — Agenda: overzicht "in behandeling" ✅
Nieuwe route `GET /agenda/team-events` (`AgendaController::teamEvents()`, `AgendaItemModel::forTeam()`) toont afspraken van alle gebruikers, met een "Alle gebruikers"-vinkje en een "Alleen tickets 'in behandeling'"-filter naast de bestaande persoon-select. Titel/tooltip van elke afspraak toont nu wie hij is en waar hij aan gekoppeld is (gekoppelde titel + status), i.p.v. alleen de kale afspraaktitel.

### Fase 4 — Tools: herstart-mail export ✅
Nieuwe module `RestartReminderController` (`GET/POST /tools/herstart-herinneringen*`): toont apparaten met `SchijfgebruikHealth::evaluate()['herstart_nodig'] === true` + gekoppelde medewerker/e-mail, met CSV-export en een "Verstuur herinneringen"-knop die per medewerker een gepersonaliseerde mail verstuurt (`{naam}`/`{apparaat}`/`{dagen}`-placeholders). Onderwerp/inhoud/cc/bcc zijn instelbaar via een nieuwe tabel `herstart_herinnering_instellingen`. `Mailer::verstuur()` ondersteunt nu ook `$cc`/`$bcc` (echte SMTP Cc-header + RCPT TO, Bcc alleen RCPT TO). Verzending gaat rechtstreeks via `Mailer` (niet via de `email_queue`), omdat die wachtrij maar één mail tegelijk toestaat en dus niet geschikt is voor een bulkverzending naar meerdere medewerkers in één keer.
Let op: een losse "CSV-import terug in het systeem" bleek niet nodig — de bestaande Schijfgebruik CSV-import (die `laatste_boot` bijwerkt) voedt de `herstart_nodig`-berekening al; er is geen apart importpad gebouwd.

### Losse verkenning — geocoding/routing API (nog geen fase toegewezen)
Nog te onderzoeken, niet gekoppeld aan een deadline; input voor een eventuele latere fase (bv. reistijd-indicatie bij Urenstaat/locaties). Coördinaten worden voor nu handmatig ingevuld naast het adresveld (zie `LocatieModel`); geen API-integratie inbouwen totdat hier bewust voor gekozen wordt.
- OpenCage Geocoding API voor adres → coördinaten (`api.opencagedata.com/geocode/v1/json`); vereist eigen API-key, rate limits nog niet uitgezocht.
- ANWB routing-API voor reistijd/afstand tussen coördinaten (incl. tolwegen) — bevestigd werkend via PowerShell:
  ```powershell
  $headers = @{ "x-anwb-caller-id" = "routing/traffic-info-web" }
  $response = Invoke-RestMethod -Uri "https://api.anwb.nl/routing/route/v1/route/car?locations=51.193943%2C6.001977%3A51.454764%2C5.389512&tollInfo=true&traffic=true&routeType=fastest&includeAlternatives=true" -Headers $headers
  $response.value | ForEach-Object {
      [PSCustomObject]@{
          RouteId = $_.id
          AfstandKm = [math]::Round($_.summary.distanceInMeters / 1000, 1)
          DuurMinuten = [math]::Round($_.summary.durationInSeconds / 60, 0)
          Wegen = $_.summary.roadNumbers -join ", "
          Tol = $_.summary.tollRoads
      }
  } | Format-Table -AutoSize
  ```
  Header `x-anwb-caller-id` is ongedocumenteerd/publiek; stabiliteit en gebruiksvoorwaarden voor productiegebruik nog niet bevestigd.


