-- Intranet database schema (MySQL / MariaDB)
-- Voer uit in HeidiSQL (open dit bestand als query-tab) of via:
-- mysql -u root vhe < database/schema.sql

CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    naam            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    rol             VARCHAR(50)  NOT NULL DEFAULT 'medewerker',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS afdelingen (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- IT > Ticket systeem
CREATE TABLE IF NOT EXISTS tickets (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    titel               VARCHAR(255) NOT NULL,
    omschrijving        TEXT NOT NULL,
    opdrachtgever_naam  VARCHAR(150) NOT NULL,
    afdeling_id         INT,
    prioriteit          VARCHAR(20) NOT NULL DEFAULT 'normaal', -- laag, normaal, hoog, kritiek
    impact              VARCHAR(100) NOT NULL DEFAULT 'Normaal',
    schatting_uren      NUMERIC(6,2),
    deadline            DATE,
    behandelaar_id      INT,
    status              VARCHAR(30) NOT NULL DEFAULT 'open', -- open, in_behandeling, wacht_op_info, opgelost, gesloten
    aangemaakt_door_id  INT,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (afdeling_id) REFERENCES afdelingen(id),
    FOREIGN KEY (behandelaar_id) REFERENCES users(id),
    FOREIGN KEY (aangemaakt_door_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ticket_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT NOT NULL,
    user_id     INT,
    opmerking   TEXT NOT NULL,
    status_van  VARCHAR(30),
    status_naar VARCHAR(30),
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- IT > Verbeterpunten
CREATE TABLE IF NOT EXISTS verbeterpunten (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    titel             VARCHAR(255) NOT NULL,
    omschrijving      TEXT NOT NULL,
    afdeling_id       INT,
    ingediend_door_id INT,
    status            VARCHAR(30) NOT NULL DEFAULT 'nieuw', -- nieuw, in_overweging, goedgekeurd, afgewezen, uitgevoerd
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (afdeling_id) REFERENCES afdelingen(id),
    FOREIGN KEY (ingediend_door_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- IT > Reflectie
CREATE TABLE IF NOT EXISTS reflecties (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titel        VARCHAR(255) NOT NULL,
    periode      VARCHAR(100) NOT NULL,
    inhoud       TEXT NOT NULL,
    gebruiker_id INT,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gebruiker_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- IT > Kennisbank
CREATE TABLE IF NOT EXISTS kennisbank_artikelen (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    titel      VARCHAR(255) NOT NULL,
    categorie  VARCHAR(100) NOT NULL DEFAULT 'Algemeen',
    inhoud     TEXT NOT NULL,
    auteur_id  INT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- IT > Uitgaven hardware
CREATE TABLE IF NOT EXISTS hardware_uitgaven (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    omschrijving        VARCHAR(255) NOT NULL,
    leverancier         VARCHAR(150),
    bedrag              NUMERIC(10,2) NOT NULL DEFAULT 0,
    aankoopdatum        DATE,
    afdeling_id         INT,
    aangevraagd_door_id INT,
    status              VARCHAR(30) NOT NULL DEFAULT 'aangevraagd', -- aangevraagd, goedgekeurd, afgekeurd, besteld, geleverd
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (afdeling_id) REFERENCES afdelingen(id),
    FOREIGN KEY (aangevraagd_door_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- CRM > Medewerkers
CREATE TABLE IF NOT EXISTS medewerkers (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    voornaam     VARCHAR(100) NOT NULL,
    achternaam   VARCHAR(100) NOT NULL,
    email        VARCHAR(150),
    telefoon     VARCHAR(50),
    functie      VARCHAR(150),
    afdeling_id  INT,
    startdatum   DATE,
    status       VARCHAR(20) NOT NULL DEFAULT 'actief', -- actief, inactief
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (afdeling_id) REFERENCES afdelingen(id)
) ENGINE=InnoDB;

-- Seed data
INSERT IGNORE INTO afdelingen (naam) VALUES ('ICT'), ('HR'), ('Finance'), ('Facilitair');

-- Gebruikers worden aangemaakt via database/seed.php (zodat het wachtwoord
-- met PHP's eigen password_hash() wordt gegenereerd, niet hardcoded hier).
