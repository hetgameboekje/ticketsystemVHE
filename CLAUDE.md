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
php database/rename_database.php <nieuwe_naam> [--drop-old]   # rename the live DB in place (no dump/restore)
```
For Hostnet (no php-cli access), `database/rename_database_hostnet.sql` does the same rename via plain `RENAME TABLE` statements, runnable from phpMyAdmin's SQL tab — see the comment header in that file for the required steps (new database must be created via Hostnet's control panel first).
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

`config/config.php` reads from `getenv()`; `.env` (gitignored, copy from `.env.example`) is one file shared across environments, holding both a `LOCAL_*` and a `HOSTNET_*` block (DB/mail/URL). `APP_ENV=local|hostnet` is the one line set manually per place — it picks which prefixed block `config.php` reads, and also fully derives `dev`/`gitPullEnabled` behavior (and, in `app/bootstrap.php`, `display_errors`): anything other than `hostnet` counts as a dev environment (auto git pull + schema-sync on `/login`, PHP errors shown), `hostnet` always has all three off. There's no separate `APP_DEV`/`APP_GIT_PULL_ENABLED`/`APP_DEBUG` key anymore — they used to be settable per environment but in practice always tracked `APP_ENV`, so they were collapsed into it.
- `App\Core\DevSync` reads `config('dev')`/`config('gitPullEnabled')` — unaffected by the collapse, still booleans.
- `APP_ENCRYPTION_KEY` — base64 32-byte key for `App\Shared\Crypto\FieldEncryptor`, used to encrypt sensitive ticket fields (`omschrijving`, `opdrachtgever_naam`). Must be identical across all environments sharing a database — rotating it makes existing encrypted tickets unreadable. Generate with `openssl rand -base64 32`. Not per-environment-prefixed.
- `{LOCAL,HOSTNET}_APP_URL` — base URL used to build absolute links in emails (e.g. reminder emails), since there's no active HTTP request context there.
- `N8N_WEBHOOK_URL` / `N8N_API_KEY` / `AI_CONFIDENCE_DREMPEL` — `App\Modules\EmailVerwerking\Services\AiAnalysisService` posts the e-mail to this n8n webhook instead of calling an AI provider directly; n8n owns ingestion/extraction/knowledge-matching/internet-lookup and must return the schema `analyseer()` validates. Not per-environment-prefixed, same as `APP_ENCRYPTION_KEY`. Empty `N8N_WEBHOOK_URL` fails closed: the analysis endpoint logs the error to `processing_logs` and leaves the e-mail on status `stored` for the next cron run, instead of throwing.

Deployment target is Hostnet shared hosting (no SSH): `.env` on that server has `APP_ENV=hostnet`, deploy via SFTP, schema applied through phpMyAdmin, `public/uploads/` must be writable.

## Frontend design direction

A new visual identity was prototyped as a static, dependency-free HTML reference (`docs/design/ticketsysteem-overzicht.html` — no build step, not served by the app) and is being rolled out into the real views module by module. Treat that file as the source of truth for tokens and component patterns; don't reinvent colors or spacing per module.

**Design tokens** (defined as CSS custom properties in `public/assets/css/app.css`, both light and dark blocks — the existing `--color-*` naming and the `data-bs-theme`/`localStorage.theme` toggle are kept as-is, not replaced):
- Dark theme backgrounds were retuned to the reference's navy/charcoal palette instead of the previous neutral-brown dark theme: `--color-background-primary:#1B2029`, `--color-background-secondary:#212733`, `--color-background-tertiary:#12151B`, borders `#3A4150`/`#2B313D`. Light theme is unchanged.
- New accent tokens `--color-accent-bg`/`--color-accent-text`/`--color-accent-border` (amber, `#E0A756` in dark) — kept separate from `--color-background-info`/`--color-text-info`, which still carries the existing "open/informational" meaning in badges (`.badge-open`) and filter chips. Don't repurpose one for the other.
- `--color-text-success` (dark) now uses the reference's teal (`#4FC1A6`), `--color-text-danger` (dark) uses its red (`#E2665A`) — chosen because those already map to the same semantic roles (done/success, danger) as the reference's status colors. The reference's violet ("informational categories") has **not** been adopted anywhere yet — that would mean changing what `--color-background-info`/`badge-open` mean everywhere they're used, which is a later, deliberate step, not a token rename.
- Typography: `--font-display` (Space Grotesk, headings/`.page-title`/`.card-title`/nav brand), `--font-body` (Inter, replaces the old `--font-sans` as the body default; `--font-sans` itself is kept as the underlying system-font fallback), `--font-mono` (IBM Plex Mono, via `.mono` utility) — loaded via Google Fonts `<link>` tags in both `app/Views/layouts/app.php` and `guest.php`.
- `.btn-accent` (new, next to the existing `.btn-primary`) is the new primary-action button style — applied to the login button (`app/Views/auth/login.php`) as the first concrete example. Don't mass-convert existing `.btn-primary` usages in one pass; swap them opportunistically whenever a view is touched for other reasons.

**Reusable component patterns** in the reference file, to reuse rather than reinvent per module once the rollout reaches them: sticky top nav with brand mark + amber primary action; KPI/stat cards (icon + value + label); status badges with a colored dot, one color per logical status (reuse the same badge-to-status mapping everywhere, don't invent new per-module colors); data tables with muted uppercase headers and row hover; horizontal numbered pipeline indicator (only for genuine sequences, e.g. the MailMind pipeline or ticket status flow — not decorative); terminal-style code blocks for commands/env vars; module cards with a monospace "path" label mirroring real file paths.

**Rollout plan (step by step, verify manually after each since there's no test suite):**
1. ✅ Done — tokens (palette, accent, fonts) added to `public/assets/css/app.css`; Google Fonts loaded in both layouts; nav active-link/dropdown-item state and the login button (`app/Views/auth/login.php`) retinted to the accent.
2. Dashboard / Overview (`app/Shared/Dashboard`, `app/Shared/Overview`) next — highest visibility, reuses `.stat`/`.card`/`.page-dashboard` classes already in `app.css`; retint hover/active states with the accent rather than adding new component classes.
3. Highest-traffic modules next: Ticket, then Kennisbank (including the EmailVerwerking/MailMind review UI, since it already conceptually matches this style).
4. Remaining domain modules (Verbeterpunt, Reflectie, HardwareUitgave, Medewerker, Voorraad, Device, Printer, CyberRisico, Uitgifte, Agenda) — batch by shared view type (list/table views together, form views together) rather than module-by-module. This is also where adopting the badge/status color mapping (teal/amber/red/violet per status) would happen, if decided — a deliberate step, not automatic.
5. Account, Beheer, Tools, Script, Schijfgebruik last — lowest-traffic admin/settings screens.

Each step is a visual/CSS pass only: keep existing routes, controllers, Dutch UI text, and behavior unchanged unless a bug is found along the way. Click through the affected module locally after each step (`php -S localhost:8000 -t public public/router.php`, check both `data-bs-theme="light"` and `"dark"`) before moving to the next one — there's no automated test suite to catch a visual regression.

## Roadmap / openstaande verbeterpunten

**Geleverd** (fases 1–4, gecontroleerd tegen de code): CRM-hiërarchie/stamboom voor medewerkers (`manager_id`/`is_keyuser`, `GET /medewerkers/hierarchie`); Urenstaat-koppeling aan keyuser/klant (`urenstaat_registraties.keyuser_id`); Agenda-teamoverzicht "in behandeling" (`GET /agenda/team-events`); Tools herstart-mail export en verzending (`RestartReminderController`, `GET/POST /tools/herstart-herinneringen*`, met `Mailer::verstuur()` cc/bcc-support).

**Geleverd — E-mail & kennisbank verwerking / MailMind** (alle 5 fases, gecontroleerd tegen de code en smoke-getest tegen een lokale database): nieuwe module `app/Modules/EmailVerwerking` met 7 tabellen (`email_import_batches`, `imported_emails`, `email_attachments`, `email_ai_analysis`, `kb_article_drafts`, `kb_article_sources`, `processing_logs`), webhook `POST /api/email-import/inbound` (scope `email_import`), cron `POST /api/email-analysis/verwerken` (scope `email_analysis`), UI onder `/email-verwerking` (rechtenmatrix-module `email_verwerking`, ook toegevoegd aan de Service-dropdown in de navigatie). De Outlook-intake (`outlook_intake.py`) post eindgebruikersmail voortaan ook naar de nieuwe pipeline naast de bestaande ticketaanmaak (best-effort, blokkeert tickets niet bij falen). **Update:** de classificatie loopt niet meer via een directe AI-provider-call, maar via een externe n8n-webhook (`Services\AiAnalysisService`, env `N8N_WEBHOOK_URL`/`N8N_API_KEY`) — n8n verzorgt ingestie, extractie, kennis-koppeling en internet-lookup, en moet exact het JSON-schema teruggeven dat `analyseer()` afdwingt. **Nog niet gedaan:** `N8N_WEBHOOK_URL` is nergens ingevuld — zonder webhook-URL blijft elke e-mail hangen op status `failed` met een duidelijke reden in `processing_logs` (fail-safe, geen crash), en moet de n8n-workflow zelf nog gebouwd en getest worden. `kb_article_drafts.kennisbank_artikel_id`/`reviewer_id` hebben bewust geen DB-niveau FOREIGN KEY (zie het commentaar in `database/xml/kb_article_drafts.xml`) — zelfde aanpak als `kennisbank_artikelen.auteur_id`/`tickets.behandelaar_id`, omdat sommige lokale databases hier al gemigreerd zijn naar een ander kolomtype dan `SchemaParser::LEGACY_PLAIN_INT_TABLES` aanneemt.

**Open aandachtspunt:** de categorie-zoekfunctie (Ticket/Kennisbank/Verbeterpunt) gebruikt een debounce van ~200ms i.p.v. de gewenste ~2s — verhogen als dit te veel requests tijdens typen oplevert.

**Losse verkenning — geocoding/routing API (geen fase toegewezen):** nog te onderzoeken voor een eventuele reistijd-indicatie bij Urenstaat/locaties; coördinaten worden nu handmatig ingevuld naast het adresveld (zie `LocatieModel`). Geen API-integratie bouwen totdat hier bewust voor gekozen wordt.
- OpenCage Geocoding API voor adres → coördinaten (`api.opencagedata.com/geocode/v1/json`); vereist eigen API-key, rate limits nog niet uitgezocht.
- ANWB routing-API voor reistijd/afstand (incl. tolwegen) via `https://api.anwb.nl/routing/route/v1/route/car` met header `x-anwb-caller-id: routing/traffic-info-web` — werkend getest via PowerShell `Invoke-RestMethod`, maar de header is ongedocumenteerd/publiek en stabiliteit/gebruiksvoorwaarden voor productiegebruik zijn niet bevestigd.

