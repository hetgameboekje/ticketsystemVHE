-- Intranet database schema (PostgreSQL)
-- Voer uit met: psql -U postgres -d intranet -f database/schema.sql

CREATE TABLE IF NOT EXISTS users (
    id              SERIAL PRIMARY KEY,
    naam            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    rol             VARCHAR(50)  NOT NULL DEFAULT 'medewerker',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS afdelingen (
    id   SERIAL PRIMARY KEY,
    naam VARCHAR(100) NOT NULL UNIQUE
);

-- IT > Ticket systeem
CREATE TABLE IF NOT EXISTS tickets (
    id                  SERIAL PRIMARY KEY,
    titel               VARCHAR(255) NOT NULL,
    omschrijving        TEXT NOT NULL,
    opdrachtgever_naam  VARCHAR(150) NOT NULL,
    afdeling_id         INTEGER REFERENCES afdelingen(id),
    prioriteit          VARCHAR(20) NOT NULL DEFAULT 'normaal', -- laag, normaal, hoog, kritiek
    impact              VARCHAR(100) NOT NULL DEFAULT 'Normaal',
    schatting_uren      NUMERIC(6,2),
    deadline            DATE,
    behandelaar_id      INTEGER REFERENCES users(id),
    status              VARCHAR(30) NOT NULL DEFAULT 'open', -- open, in_behandeling, wacht_op_info, opgelost, gesloten
    aangemaakt_door_id  INTEGER REFERENCES users(id),
    created_at          TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS ticket_logs (
    id          SERIAL PRIMARY KEY,
    ticket_id   INTEGER NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    user_id     INTEGER REFERENCES users(id),
    opmerking   TEXT NOT NULL,
    status_van  VARCHAR(30),
    status_naar VARCHAR(30),
    created_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- IT > Verbeterpunten
CREATE TABLE IF NOT EXISTS verbeterpunten (
    id               SERIAL PRIMARY KEY,
    titel            VARCHAR(255) NOT NULL,
    omschrijving     TEXT NOT NULL,
    afdeling_id      INTEGER REFERENCES afdelingen(id),
    ingediend_door_id INTEGER REFERENCES users(id),
    status           VARCHAR(30) NOT NULL DEFAULT 'nieuw', -- nieuw, in_overweging, goedgekeurd, afgewezen, uitgevoerd
    created_at       TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMP NOT NULL DEFAULT NOW()
);

-- IT > Reflectie
CREATE TABLE IF NOT EXISTS reflecties (
    id           SERIAL PRIMARY KEY,
    titel        VARCHAR(255) NOT NULL,
    periode      VARCHAR(100) NOT NULL,
    inhoud       TEXT NOT NULL,
    gebruiker_id INTEGER REFERENCES users(id),
    created_at   TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP NOT NULL DEFAULT NOW()
);

-- IT > Kennisbank
CREATE TABLE IF NOT EXISTS kennisbank_artikelen (
    id         SERIAL PRIMARY KEY,
    titel      VARCHAR(255) NOT NULL,
    categorie  VARCHAR(100) NOT NULL DEFAULT 'Algemeen',
    inhoud     TEXT NOT NULL,
    auteur_id  INTEGER REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- IT > Uitgaven hardware
CREATE TABLE IF NOT EXISTS hardware_uitgaven (
    id                 SERIAL PRIMARY KEY,
    omschrijving       VARCHAR(255) NOT NULL,
    leverancier        VARCHAR(150),
    bedrag             NUMERIC(10,2) NOT NULL DEFAULT 0,
    aankoopdatum       DATE,
    afdeling_id        INTEGER REFERENCES afdelingen(id),
    aangevraagd_door_id INTEGER REFERENCES users(id),
    status             VARCHAR(30) NOT NULL DEFAULT 'aangevraagd', -- aangevraagd, goedgekeurd, afgekeurd, besteld, geleverd
    created_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP NOT NULL DEFAULT NOW()
);

-- CRM > Medewerkers
CREATE TABLE IF NOT EXISTS medewerkers (
    id           SERIAL PRIMARY KEY,
    voornaam     VARCHAR(100) NOT NULL,
    achternaam   VARCHAR(100) NOT NULL,
    email        VARCHAR(150),
    telefoon     VARCHAR(50),
    functie      VARCHAR(150),
    afdeling_id  INTEGER REFERENCES afdelingen(id),
    startdatum   DATE,
    status       VARCHAR(20) NOT NULL DEFAULT 'actief', -- actief, inactief
    created_at   TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Seed data
INSERT INTO afdelingen (naam) VALUES ('ICT'), ('HR'), ('Finance'), ('Facilitair')
ON CONFLICT (naam) DO NOTHING;

-- Gebruikers worden aangemaakt via database/seed.php (zodat het wachtwoord
-- met PHP's eigen password_hash() wordt gegenereerd, niet hardcoded hier).
