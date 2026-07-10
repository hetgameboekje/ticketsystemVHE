# Outlook-intake voor IT@vhe.nl

Leest ongelezen mail uit de gedeelde mailbox IT@vhe.nl via Outlook Classic (COM/pywin32) en zet ze
om naar tickets op het intranet. Verstuurt of ontvangt zelf geen mail — leest alleen wat er al in
Outlook staat.

Twee mailtypes worden herkend, op basis van het afzenderadres:
- **ACA-case-updates** → `POST /api/tickets/vanuit-aca-email`. Het CAS-nummer in het onderwerp
  koppelt updates aan hetzelfde ticket (nieuw ticket bij eerste keer, daarna een opmerking op het
  bestaande ticket; "Afmelding" zet de status op `afgehandeld`).
- **Overige mail** (eindgebruikers/key-users) → `POST /api/tickets/vanuit-email`, altijd een nieuw
  ticket (met dedupe op afzender+titel binnen 30 dagen, zie `TicketEmailIntakeController`).

## Vereisten

- Outlook Classic, met de gedeelde mailbox IT@vhe.nl toegevoegd aan je eigen profiel.
- Python 3.x op dezelfde Windows-pc.
- `pip install -r requirements.txt`

## Configuratie

1. Maak op het intranet (ingelogd als admin) een API-sleutel aan via **Beheer > API-sleutels >
   Nieuwe sleutel**, met de scopes **"E-mailintake tickets"** en **"ACA-case-updates"** aangevinkt.
   De sleutel wordt maar één keer getoond — kopieer hem direct.
2. Kopieer `config.example.ini` naar `config.ini`.
3. Vul in:
   - `api_base_url` — URL van het intranet.
   - `api_key` — de sleutel uit stap 1.
   - `mailbox_naam` / `postvak_naam` — moeten exact overeenkomen met de mapnamen zoals Outlook ze
     toont (let op: Nederlandstalig Outlook toont "Postvak IN", niet "Inbox").
   - `aca_sender_email` — het afzenderadres van de ACA-case-updatemails.

`config.ini` bevat de API-key en staat daarom in `.gitignore` — nooit committen. Een sleutel die
gelekt is of niet meer nodig is, trek je in via Beheer > API-sleutels (intrekken kan altijd
ongedaan gemaakt worden via "heractiveren", zolang je de sleutel zelf niet opnieuw hoeft in te
vullen — de sleutelwaarde zelf wordt na aanmaken niet meer opnieuw getoond).

## Handmatig testen

```powershell
python outlook_intake.py
```

Zorg dat Outlook open staat en ingelogd is op het profiel met de IT@vhe.nl-mailbox. Fouten en
verwerkte mails komen in `outlook_intake.log` (in dezelfde map) én op het scherm.

## Draaien via Windows Taakplanner

1. Maak een nieuwe taak aan (niet "basistaak", zodat je alle opties hebt).
2. Trigger: herhaal elke bijv. 5 minuten, voor onbepaalde tijd.
3. Actie: programma starten:
   - Programma: pad naar `python.exe` (of `pythonw.exe` om het consolevenster te onderdrukken)
   - Argumenten: `outlook_intake.py`
   - Beginnen in: de map van dit script (`scripts\automation\outlook-intake`)
4. Voer de taak uit **onder jouw eigen gebruikersaccount**, met "alleen uitvoeren als gebruiker is
   aangemeld" — de taak heeft een actieve Outlook-sessie nodig (COM-automatisering werkt niet
   headless/als systeemaccount zonder ingelogde Outlook-desktopsessie).
5. Zet "Taak stoppen als deze langer dan ..." op iets ruims (bijv. 10 minuten), zodat een
   vastgelopen run niet blijft hangen tot de volgende trigger.

## Nieuwe ACA-afzender of subjectformaat

Het subjectformaat wordt geparsed met `ACA_SUBJECT_RE` in `outlook_intake.py`, gebaseerd op
voorbeelden als:

```
CAS-109512-R6Z2W3 - Nieuw - Uitval netwerk ACA:000134101
CAS-109512-R6Z2W3 - Update - Uitval netwerk ACA:004133688
CAS-109512-R6Z2W3 - Afmelding - Uitval netwerk ACA:000134120
```

Mail van het geconfigureerde `aca_sender_email` met een onderwerp dat niet aan dit patroon voldoet,
wordt overgeslagen en blijft ongelezen (zichtbaar als waarschuwing in het logbestand) — die moet je
dan handmatig beoordelen.
