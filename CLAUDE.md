# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Leen van Punt Intranet Ticketsysteem — a modular intranet in plain PHP (no framework), MySQL/MariaDB. Core domain is ticket management, plus supporting modules (kennisbank, voorraad, agenda, CRM, etc.). Documentation, code comments, routes and UI text are in Dutch; keep new user-facing text and route segments in Dutch for consistency.

There is no composer.json, no autoloaded third-party dependencies, and no automated test suite — verification is done by running the app locally and exercising the affected flow.

## Commands

Run the app locally (no Apache/Laragon required):
```bash
php -S localhost:8000 -t public public/router.php
```

Database:
```bash
php database/parse.php     # regenerate database/.parsed/schema.sql from database/xml/*.xml
php database/seed.php      # seed demo user (timo@bergthaler.nl / demo123, override via SEED_USER_* in .env)
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
- `app/Modules/<Name>` — one folder per business module (Ticket, Verbeterpunt, Reflectie, Kennisbank, HardwareUitgave, Medewerker, Voorraad, Device, Printer, CyberRisico, Uitgifte, Agenda, Account, Beheer, Tools, Script, Schijfgebruik, EmailVerwerking). Each module has its own `*Controller.php`, `Models/*Model.php` (extends `App\Core\Model`), and `Views/<Name>View/*.php`. Most modules with loggable activity have a paired `*LogController` + log model (ticket_logs, verbeterpunt_logs, etc.) following the same pattern — copy an existing module's shape (e.g. Reflectie or CyberRisico) when adding a new one.
- `app/Modules/EmailVerwerking` — "E-mail & kennisbank verwerking" (MailMind): turns incoming support e-mail into logged history, AI classification, and Kennisbank drafts pending review. `EmailImportController`/`EmailAnalysisController` are machine-to-machine (API-key scopes `email_import`/`email_analysis`, same webhook/cron pattern as `TicketEmailIntakeController`/`AutomationController`); `EmailVerwerkingController` is the session-auth UI (rechtenmatrix module `email_verwerking`). `Services\AiAnalysisService` posts each e-mail to an external n8n webhook (`config/config.php` 'n8n', `N8N_WEBHOOK_URL`/`N8N_API_KEY`) which performs the actual classification/extraction and is expected to return the same fixed JSON schema `analyseer()` validates; `Services\KbDraftGenerator` turns a classification into (or attaches it to an existing) `kb_article_drafts` row, later published into `kennisbank_artikelen` via `KbArticleDraftModel::publiceer()`. The Outlook intake script (`scripts/automation/outlook-intake/outlook_intake.py`) posts end-user mail to `/api/email-import/inbound` alongside creating the ticket (best-effort, never blocks ticket creation); a separate Taakplanner-style cron hits `/api/email-analysis/verwerken` to run AI analysis in batches.
- `app/Views/layouts` — shared page layouts included by module views.

Routing convention: most modules are wired via the `$modules` array in `public/index.php`, which mechanically registers the standard `index/create/store/show/edit/update/destroy` routes (`GET /X`, `GET /X/create`, `POST /X`, `GET /X/{id}`, `GET /X/{id}/edit`, `POST /X/{id}`, `POST /X/{id}/verwijderen`) for each controller. Module-specific extra routes (log entries, exports, nested actions) are added individually below that loop.

### Config and environments

`config/config.php` reads from `getenv()` with Laragon-friendly local defaults; `.env` (gitignored, copy from `.env.example`) overrides per environment. Key flags:
- `APP_DEV` — when true, hitting `/login` triggers `DevSync`: automatic `git pull` + database parse/apply. Must be `false` in production.
- `APP_GIT_PULL_ENABLED` — separately gates the `exec('git pull')` call, since shared hosts (e.g. Hostnet) often disable `exec()`/have no shell access; database parsing still works with this off.
- `APP_ENCRYPTION_KEY` — base64 32-byte key for `App\Shared\Crypto\FieldEncryptor`, used to encrypt sensitive ticket fields (`omschrijving`, `opdrachtgever_naam`). Must be identical across all environments sharing a database — rotating it makes existing encrypted tickets unreadable. Generate with `openssl rand -base64 32`.
- `APP_URL` — base URL used to build absolute links in emails (e.g. reminder emails), since there's no active HTTP request context there.
- `N8N_WEBHOOK_URL` / `N8N_API_KEY` / `AI_CONFIDENCE_DREMPEL` — `App\Modules\EmailVerwerking\Services\AiAnalysisService` posts the e-mail to this n8n webhook instead of calling an AI provider directly; n8n owns ingestion/extraction/knowledge-matching/internet-lookup and must return the schema `analyseer()` validates. Not per-environment-prefixed, same as `APP_ENCRYPTION_KEY`. Empty `N8N_WEBHOOK_URL` fails closed: the analysis endpoint logs the error to `processing_logs` and leaves the e-mail on status `stored` for the next cron run, instead of throwing.

Deployment target is Hostnet shared hosting (no SSH): `APP_DEV=false`, `APP_GIT_PULL_ENABLED=false`, deploy via SFTP, schema applied through phpMyAdmin, `public/uploads/` must be writable.

## Roadmap / openstaande verbeterpunten

**Geleverd** (fases 1–4, gecontroleerd tegen de code): CRM-hiërarchie/stamboom voor medewerkers (`manager_id`/`is_keyuser`, `GET /medewerkers/hierarchie`); Urenstaat-koppeling aan keyuser/klant (`urenstaat_registraties.keyuser_id`); Agenda-teamoverzicht "in behandeling" (`GET /agenda/team-events`); Tools herstart-mail export en verzending (`RestartReminderController`, `GET/POST /tools/herstart-herinneringen*`, met `Mailer::verstuur()` cc/bcc-support).

**Geleverd — E-mail & kennisbank verwerking / MailMind** (alle 5 fases, gecontroleerd tegen de code en smoke-getest tegen een lokale database): nieuwe module `app/Modules/EmailVerwerking` met 7 tabellen (`email_import_batches`, `imported_emails`, `email_attachments`, `email_ai_analysis`, `kb_article_drafts`, `kb_article_sources`, `processing_logs`), webhook `POST /api/email-import/inbound` (scope `email_import`), cron `POST /api/email-analysis/verwerken` (scope `email_analysis`), UI onder `/email-verwerking` (rechtenmatrix-module `email_verwerking`, ook toegevoegd aan de Service-dropdown in de navigatie). De Outlook-intake (`outlook_intake.py`) post eindgebruikersmail voortaan ook naar de nieuwe pipeline naast de bestaande ticketaanmaak (best-effort, blokkeert tickets niet bij falen). **Update:** de classificatie loopt niet meer via een directe AI-provider-call, maar via een externe n8n-webhook (`Services\AiAnalysisService`, env `N8N_WEBHOOK_URL`/`N8N_API_KEY`) — n8n verzorgt ingestie, extractie, kennis-koppeling en internet-lookup, en moet exact het JSON-schema teruggeven dat `analyseer()` afdwingt. **Nog niet gedaan:** `N8N_WEBHOOK_URL` is nergens ingevuld — zonder webhook-URL blijft elke e-mail hangen op status `failed` met een duidelijke reden in `processing_logs` (fail-safe, geen crash), en moet de n8n-workflow zelf nog gebouwd en getest worden. `kb_article_drafts.kennisbank_artikel_id`/`reviewer_id` hebben bewust geen DB-niveau FOREIGN KEY (zie het commentaar in `database/xml/kb_article_drafts.xml`) — zelfde aanpak als `kennisbank_artikelen.auteur_id`/`tickets.behandelaar_id`, omdat sommige lokale databases hier al gemigreerd zijn naar een ander kolomtype dan `SchemaParser::LEGACY_PLAIN_INT_TABLES` aanneemt.

**Open aandachtspunt:** de categorie-zoekfunctie (Ticket/Kennisbank/Verbeterpunt) gebruikt een debounce van ~200ms i.p.v. de gewenste ~2s — verhogen als dit te veel requests tijdens typen oplevert.

**Losse verkenning — geocoding/routing API (geen fase toegewezen):** nog te onderzoeken voor een eventuele reistijd-indicatie bij Urenstaat/locaties; coördinaten worden nu handmatig ingevuld naast het adresveld (zie `LocatieModel`). Geen API-integratie bouwen totdat hier bewust voor gekozen wordt.
- OpenCage Geocoding API voor adres → coördinaten (`api.opencagedata.com/geocode/v1/json`); vereist eigen API-key, rate limits nog niet uitgezocht.
- ANWB routing-API voor reistijd/afstand (incl. tolwegen) via `https://api.anwb.nl/routing/route/v1/route/car` met header `x-anwb-caller-id: routing/traffic-info-web` — werkend getest via PowerShell `Invoke-RestMethod`, maar de header is ongedocumenteerd/publiek en stabiliteit/gebruiksvoorwaarden voor productiegebruik zijn niet bevestigd.

