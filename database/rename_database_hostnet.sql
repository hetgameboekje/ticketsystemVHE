-- Hernoemt de live Hostnet-database van "vhe"-stijl naar een generieke naam ("leenvanpunt"),
-- als tegenhanger van database/rename_database.php (dat vereist php-cli, wat op Hostnet
-- shared hosting niet beschikbaar is). Verplaatst ALLEEN de tabellen (RENAME TABLE) — er wordt
-- geen rij-inhoud gelezen, gewijzigd of verwijderd. Kolomnamen bevatten geen "vhe" (gecontroleerd
-- tegen de lokale database), dus dit script hoeft niets in de structuur zelf aan te passen.
--
-- VOORWAARDE: de doeldatabase (bv. "xxxxxxxx_leenvanpunt") moet al bestaan. Op Hostnet-shared-
-- hosting kun je meestal geen nieuwe database aanmaken via een SQL-query (CREATE DATABASE vereist
-- vaak rechten die de accountgebruiker niet heeft) — maak 'm eerst aan via het Hostnet-controlepaneel
-- (Databases), met dezelfde table-user/rechten als de huidige database.
--
-- Gebruik (phpMyAdmin):
--   1. Maak de nieuwe database aan via het Hostnet-paneel, bv. "xxxxxxxx_leenvanpunt".
--   2. Vervang hieronder "xxxxxxxx_vhe" door je huidige live databasenaam en
--      "xxxxxxxx_leenvanpunt" door de zojuist aangemaakte naam (moet exact overeenkomen met
--      wat het Hostnet-paneel je gegeven heeft, incl. accountprefix).
--   3. Open phpMyAdmin, kies een van beide databases (maakt niet uit welke) in de linkerkolom,
--      ga naar het tabblad "SQL" en plak/voer dit hele bestand uit.
--   4. Controleer daarna in phpMyAdmin dat de nieuwe database alle 54 tabellen bevat en de oude
--      database leeg is.
--   5. Zet HOSTNET_DB_DATABASE=xxxxxxxx_leenvanpunt in de .env op de live server en herstart
--      (herlaad) de applicatie (bv. via een dummy-verzoek, PHP heeft geen aparte restart nodig).
--   6. Verwijder de oude (nu lege) database pas als je zeker weet dat alles werkt.

RENAME TABLE
    xxxxxxxx_vhe.afdelingen                        TO xxxxxxxx_leenvanpunt.afdelingen,
    xxxxxxxx_vhe.agenda_items                      TO xxxxxxxx_leenvanpunt.agenda_items,
    xxxxxxxx_vhe.api_keys                          TO xxxxxxxx_leenvanpunt.api_keys,
    xxxxxxxx_vhe.cyberrisico_logs                  TO xxxxxxxx_leenvanpunt.cyberrisico_logs,
    xxxxxxxx_vhe.cyberrisicos                      TO xxxxxxxx_leenvanpunt.cyberrisicos,
    xxxxxxxx_vhe.device_software                   TO xxxxxxxx_leenvanpunt.device_software,
    xxxxxxxx_vhe.devices                           TO xxxxxxxx_leenvanpunt.devices,
    xxxxxxxx_vhe.email_ai_analysis                 TO xxxxxxxx_leenvanpunt.email_ai_analysis,
    xxxxxxxx_vhe.email_attachments                 TO xxxxxxxx_leenvanpunt.email_attachments,
    xxxxxxxx_vhe.email_import_batches              TO xxxxxxxx_leenvanpunt.email_import_batches,
    xxxxxxxx_vhe.email_queue                       TO xxxxxxxx_leenvanpunt.email_queue,
    xxxxxxxx_vhe.email_signatures                  TO xxxxxxxx_leenvanpunt.email_signatures,
    xxxxxxxx_vhe.hardware_uitgaven                 TO xxxxxxxx_leenvanpunt.hardware_uitgaven,
    xxxxxxxx_vhe.herstart_herinnering_instellingen TO xxxxxxxx_leenvanpunt.herstart_herinnering_instellingen,
    xxxxxxxx_vhe.imported_emails                   TO xxxxxxxx_leenvanpunt.imported_emails,
    xxxxxxxx_vhe.installatie_applicaties           TO xxxxxxxx_leenvanpunt.installatie_applicaties,
    xxxxxxxx_vhe.installatie_opdracht_items        TO xxxxxxxx_leenvanpunt.installatie_opdracht_items,
    xxxxxxxx_vhe.installatie_opdracht_profielen    TO xxxxxxxx_leenvanpunt.installatie_opdracht_profielen,
    xxxxxxxx_vhe.installatie_opdrachten            TO xxxxxxxx_leenvanpunt.installatie_opdrachten,
    xxxxxxxx_vhe.installatie_profiel_items         TO xxxxxxxx_leenvanpunt.installatie_profiel_items,
    xxxxxxxx_vhe.installatie_profielen             TO xxxxxxxx_leenvanpunt.installatie_profielen,
    xxxxxxxx_vhe.kb_article_drafts                 TO xxxxxxxx_leenvanpunt.kb_article_drafts,
    xxxxxxxx_vhe.kb_article_sources                TO xxxxxxxx_leenvanpunt.kb_article_sources,
    xxxxxxxx_vhe.kennisbank_artikelen              TO xxxxxxxx_leenvanpunt.kennisbank_artikelen,
    xxxxxxxx_vhe.kennisbank_logs                   TO xxxxxxxx_leenvanpunt.kennisbank_logs,
    xxxxxxxx_vhe.locatie_gebruikers                TO xxxxxxxx_leenvanpunt.locatie_gebruikers,
    xxxxxxxx_vhe.locaties                          TO xxxxxxxx_leenvanpunt.locaties,
    xxxxxxxx_vhe.login_attempts                    TO xxxxxxxx_leenvanpunt.login_attempts,
    xxxxxxxx_vhe.medewerkers                       TO xxxxxxxx_leenvanpunt.medewerkers,
    xxxxxxxx_vhe.paginabezoeken                    TO xxxxxxxx_leenvanpunt.paginabezoeken,
    xxxxxxxx_vhe.phonebook_jobs                    TO xxxxxxxx_leenvanpunt.phonebook_jobs,
    xxxxxxxx_vhe.printers                          TO xxxxxxxx_leenvanpunt.printers,
    xxxxxxxx_vhe.processing_logs                   TO xxxxxxxx_leenvanpunt.processing_logs,
    xxxxxxxx_vhe.rechten                           TO xxxxxxxx_leenvanpunt.rechten,
    xxxxxxxx_vhe.reflectie_logs                    TO xxxxxxxx_leenvanpunt.reflectie_logs,
    xxxxxxxx_vhe.reflecties                        TO xxxxxxxx_leenvanpunt.reflecties,
    xxxxxxxx_vhe.schijfgebruik_devices             TO xxxxxxxx_leenvanpunt.schijfgebruik_devices,
    xxxxxxxx_vhe.schijfgebruik_schijven            TO xxxxxxxx_leenvanpunt.schijfgebruik_schijven,
    xxxxxxxx_vhe.scripts                           TO xxxxxxxx_leenvanpunt.scripts,
    xxxxxxxx_vhe.signature_logos                   TO xxxxxxxx_leenvanpunt.signature_logos,
    xxxxxxxx_vhe.software_inventaris               TO xxxxxxxx_leenvanpunt.software_inventaris,
    xxxxxxxx_vhe.ticket_herinneringen              TO xxxxxxxx_leenvanpunt.ticket_herinneringen,
    xxxxxxxx_vhe.ticket_kennisbank_artikelen       TO xxxxxxxx_leenvanpunt.ticket_kennisbank_artikelen,
    xxxxxxxx_vhe.ticket_logs                       TO xxxxxxxx_leenvanpunt.ticket_logs,
    xxxxxxxx_vhe.ticket_tijdregistraties           TO xxxxxxxx_leenvanpunt.ticket_tijdregistraties,
    xxxxxxxx_vhe.tickets                           TO xxxxxxxx_leenvanpunt.tickets,
    xxxxxxxx_vhe.uitgiften                         TO xxxxxxxx_leenvanpunt.uitgiften,
    xxxxxxxx_vhe.urenstaat_registraties            TO xxxxxxxx_leenvanpunt.urenstaat_registraties,
    xxxxxxxx_vhe.users                             TO xxxxxxxx_leenvanpunt.users,
    xxxxxxxx_vhe.verbeterpunt_logs                 TO xxxxxxxx_leenvanpunt.verbeterpunt_logs,
    xxxxxxxx_vhe.verbeterpunt_tijdregistraties     TO xxxxxxxx_leenvanpunt.verbeterpunt_tijdregistraties,
    xxxxxxxx_vhe.verbeterpunten                    TO xxxxxxxx_leenvanpunt.verbeterpunten,
    xxxxxxxx_vhe.voorraad_items                    TO xxxxxxxx_leenvanpunt.voorraad_items,
    xxxxxxxx_vhe.voorraad_types                    TO xxxxxxxx_leenvanpunt.voorraad_types;

-- Let op: als je Hostnet-account maar 1 database mag hebben (afhankelijk van je pakket), lukt
-- bovenstaande niet totdat er een extra database bij je pakket is toegevoegd. Neem in dat geval
-- contact op met Hostnet-support, of gebruik de mysqldump/import-route (export via phpMyAdmin,
-- databasenaam aanpassen in het exportbestand, importeren in de nieuwe database) als alternatief.
