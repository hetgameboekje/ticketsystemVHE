# Intranet

Plain-PHP intranet (geen framework) met PostgreSQL. Het ticketsysteem (`/tickets`) is de
volledig uitgewerkte module; de navigatie is opgezet zodat verdere onderdelen er als losse
modules naast komen te staan.

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

## Setup

1. Database aanmaken en schema laden:
   ```
   createdb intranet
   psql -U postgres -d intranet -f database/schema.sql
   ```
2. Verbindingsgegevens staan in `config/config.php` (standaard: localhost:5432,
   gebruiker `postgres`, wachtwoord `postgres`, database `intranet`). Aanpassen kan
   ook via environment variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
3. Vereist de PHP-extensie `pdo_pgsql` (in `php.ini`: `extension=pdo_pgsql`).
4. Demo-gebruikers aanmaken:
   ```
   php database/seed.php
   ```
   Dit maakt 3 gebruikers met wachtwoord `wachtwoord123`.
5. Server starten:
   ```
   php -S localhost:8000 -t public public/router.php
   ```
   en open http://localhost:8000 — log in met `admin@intranet.local` / `wachtwoord123`.

   Draai je liever via Apache/XAMPP? Zet de `public/`-map als document root, de
   meegeleverde `.htaccess` regelt dan de pretty URLs.
