# Intranet

Plain-PHP intranet (geen framework) met MySQL/MariaDB (Laragon). Het ticketsysteem (`/tickets`)
is de volledig uitgewerkte module; de navigatie is opgezet zodat verdere onderdelen er als
losse modules naast komen te staan.

Navigatie:
- **IT** — Ticket systeem, Verbeterpunten, Reflectie, Kennisbank, Uitgaven hardware
- **CRM** — Medewerkers

## Structuur

```
app/
  Core/        Database, Model, Controller, CrudController, Router
  Models/      Ticket, TicketLog, Verbeterpunt, Reflectie, KennisbankArtikel, HardwareUitgave, Medewerker, User, Afdeling
  Controllers/ Eén controller per module, allemaal CRUD via CrudController
  Views/       Per module: index/create/show/edit + layouts/navbar
database/
  schema.sql   Tabellen + seed-afdelingen
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
