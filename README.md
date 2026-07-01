# Intranet

Plain-PHP intranet (geen framework) met MySQL/MariaDB (Laragon). Het ticketsysteem (`/tickets`)
is de volledig uitgewerkte module; de navigatie is opgezet zodat verdere onderdelen er als
losse modules naast komen te staan.

Navigatie:
- **IT** — Ticket systeem, Verbeterpunten, Reflectie, Kennisbank, Uitgaven hardware
- **CRM** — Medewerkers

## Structuur

Elke extensie/module heeft zijn eigen map met daarin zijn eigen Controller, Models/ en
Views/ — geen centrale Models/Controllers/Views-mappen meer per laag, maar per module.

```
app/
  Core/                  Database, Model, Controller, CrudController, Router (gedeelde basis)
  Shared/
    Auth/                AuthController (login/logout)
    Dashboard/            DashboardController
    User/Models/          UserModel
    Afdeling/Models/       AfdelingModel
  Modules/
    Ticket/
      TicketController.php
      TicketLogController.php
      Models/             TicketModel, TicketLogModel
      Views/TicketView/    index, create, edit, show
    Verbeterpunt/          (zelfde opbouw)
    Reflectie/             (zelfde opbouw)
    Kennisbank/            (zelfde opbouw)
    HardwareUitgave/       (zelfde opbouw)
    Medewerker/            (zelfde opbouw)
  Views/
    layouts/               app.php (navbar + dropdowns), guest.php
    auth/, dashboard/, partials/   gedeelde/globale views
database/
  xml/         Tabel-ontwerp per tabel (1 .xml per tabel) — bron voor het schema
  parse.php    Genereert database/.parsed/schema.sql uit database/xml/*.xml
  .parsed/     Output van parse.php (niet handmatig bewerken, niet in git)
  schema.sql   Handmatig schema (huidige stand van de database; kan vervangen worden door .parsed/schema.sql)
  seed.php     Maakt demo-gebruikers aan (wachtwoord_hash via password_hash())
public/
  index.php    Front controller + routes
  router.php   Voor php -S (built-in server)
  .htaccess    Voor Apache
```

## Setup (Laragon)

1. Database `vhe` aanmaken (bijv. in HeidiSQL: rechtsklik → Create new → Database) en
   `database/schema.sql` erop uitvoeren (open het bestand als query-tab in HeidiSQL en
   voer uit, of via de `mysql`-CLI: `mysql -u root vhe < database/schema.sql`).

   Een veld toevoegen aan een tabel? Bewerk het bijbehorende bestand in `database/xml/`
   (zie `database/xml/README.md` voor het formaat), draai `php database/parse.php`, en
   voer het resultaat (`database/.parsed/schema.sql`) uit op je database.
2. Verbindingsgegevens staan in `config/config.php` (standaard: Laragon-defaults —
   `127.0.0.1:3306`, gebruiker `root`, geen wachtwoord, database `vhe`). Aanpassen kan
   ook via environment variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Vereist de PHP-extensie `pdo_mysql` (in Laragon meestal al actief; te checken via
   rechtsklik Laragon-tray → PHP → Extensions).
4. Demo-gebruikers aanmaken:
   ```
   php database/seed.php
   ```
   Dit maakt 3 gebruikers met wachtwoord `wachtwoord123`.
5. Toegang tot de site:
   - **Via Laragon's auto-vhost**: zet dit project in Laragon's `www`-map, start Apache +
     MySQL, en open de gegenereerde `*.test`-URL (bijv. `http://ticketsystemvhe.test`).
     De root-`.htaccess` stuurt het verkeer automatisch door naar `public/`.
   - **Via PHP's eigen server** (geen Apache nodig):
     ```
     php -S localhost:8000 -t public public/router.php
     ```
     en open http://localhost:8000.

   Log in met `admin@intranet.local` / `wachtwoord123`.

## Omgevingsinstellingen (.env)

`config/config.php` leest DB-credentials en twee gedragsvlaggen via `getenv()`, met
Laragon-defaults als fallback. Kopieer `.env.example` naar `.env` (staat in `.gitignore`,
wordt dus nooit gecommit) om dit per omgeving te overschrijven zonder `config/config.php`
aan te passen:

- `APP_DEV` — `true` (lokaal/dev): bij het laden van `/login` wordt automatisch `git pull`
  gedaan en het databaseschema geparsed + toegepast (zie `App\Core\DevSync`). `false`
  (productie): dit gebeurt alleen nog handmatig via de Beheer-pagina.
- `APP_GIT_PULL_ENABLED` — `true` op servers met shell/exec-toegang (Docker/VPS). `false`
  op servers zonder shell-toegang (zoals Hostnet shared webhosting) — schakelt de
  "Git pull"-knop en het git-onderdeel van dev-sync uit; database parsen blijft werken
  (gebruikt alleen PDO, geen shell).

## Deployen naar Hostnet

**Standaard Hostnet shared webhosting (cPanel/DirectAdmin, geen SSH):**

1. Zet `APP_DEV=false` en `APP_GIT_PULL_ENABLED=false` in `.env` op de server — er is geen
   shell-toegang, dus `exec('git pull')` zou daar toch nooit werken (en kan zelfs een PHP-
   waarschuwing geven als `exec()` in `disable_functions` staat).
2. Upload de code via SFTP/FTP (of de bestandsbeheerder in het Hostnet-paneel). Omdat er
   geen SSH is, kun je hier niet `git pull` op de server draaien — bij elke wijziging moet
   je opnieuw uploaden, of de repo lokaal clonen/updaten en dan syncen.
3. Zorg dat de domeinroot naar de map met dit project wijst; de root-`.htaccess` stuurt
   verkeer automatisch door naar `public/`. Zet de webroot dus **niet** direct op `public/`
   zelf, tenzij het Hostnet-paneel dat toestaat — beide werkt met de meegeleverde `.htaccess`.
4. Maak een MySQL-database + gebruiker aan via het Hostnet-paneel, en zet die gegevens in
   `.env` (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — Hostnet's MySQL-host is
   meestal niet `127.0.0.1`, check het paneel).
5. Draai `php database/parse.php` lokaal, en voer het resulterende
   `database/.parsed/schema.sql` uit via phpMyAdmin op Hostnet (of gebruik de "Database
   parsen"-knop op de Beheer-pagina nadat je bent ingelogd — die gebruikt alleen PDO, geen
   shell, en werkt dus ook zonder SSH).
6. Zorg dat `pdo_mysql` actief staat in de PHP-versie die je in het Hostnet-paneel kiest.
7. Zorg dat `public/uploads/` beschrijfbaar is voor de webserver (profielfoto's).
8. Zet **HTTPS aan** (Hostnet biedt gratis Let's Encrypt in het paneel) — het inlogformulier
   verstuurt wachtwoorden, dit hoort niet onbeveiligd over HTTP te gaan.

**Persoonlijke/Docker-hosting (met shell-toegang):**

Hier kan `APP_GIT_PULL_ENABLED=true` blijven staan en werkt de "Git pull"-knop op de
Beheer-pagina echt. Zorg dat de container/VM een geconfigureerde git-remote heeft (met
toegang tot de repo — SSH deploy key aanbevolen boven een token in de remote-URL) en dat
`git` binnen de PHP-proces-omgeving (dezelfde user als de webserver) uitvoerbaar is.
