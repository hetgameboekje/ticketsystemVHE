"""
Leest ongelezen mail uit de gedeelde mailbox IT@bergthaler.dev (via Outlook Classic/pywin32) en zet ze om
naar tickets op het intranet, door te posten naar de bestaande e-mail-intake-endpoints:

- Eindgebruiker/key-user-mail  -> POST /api/tickets/vanuit-email      (TicketEmailIntakeController::store)
- ACA-case-update-mail         -> POST /api/tickets/vanuit-aca-email  (TicketEmailIntakeController::storeAcaUpdate)

Verstuurt of ontvangt zelf geen mail — leest alleen de mailbox die al in Outlook openstaat en
verwerkt de inhoud. Bedoeld om periodiek te draaien via Windows Taakplanner (geen eigen loop/sleep).

Alleen "Unread" items worden bekeken; na succesvolle verwerking wordt het item op gelezen gezet.
Zo dient de Unread-vlag als idempotentie-marker: bij een fout blijft de mail ongelezen en wordt hij
bij de volgende run opnieuw geprobeerd.
"""

from __future__ import annotations

import configparser
import logging
import re
import sys
from pathlib import Path

import requests
import win32com.client

CONFIG_PATH = Path(__file__).with_name("config.ini")
LOG_PATH = Path(__file__).with_name("outlook_intake.log")

# Subjectformaat van ACA-case-updatemails, bv.:
#   "CAS-109512-R6Z2W3 - Update - Uitval netwerk ACA:004133688"
# Het CAS-nummer blijft constant over de levensduur van de case; het ACA-nummer wijzigt per mail.
ACA_SUBJECT_RE = re.compile(
    r"^(?P<cas>CAS-[\w-]+)\s*-\s*(?P<actie>Nieuw|Update|Afmelding)\s*-\s*(?P<titel>.+?)\s*ACA:(?P<aca>\d+)\s*$",
    re.IGNORECASE,
)

# olMail = 43 (Class-waarde van MailItem); overslaan van agenda-uitnodigingen e.d.
OL_MAIL_ITEM_CLASS = 43

# Namen waarop gescand wordt om automatisch een behandelaar aan het ticket te koppelen
# (matcht tegen users.naam via een LIKE-zoekopdracht op de backend, bv. "Timo" -> "Timo Bergthaler").
BEHANDELAAR_NAMEN = ["Timo", "Frank"]
BEHANDELAAR_RE = re.compile(r"\b(" + "|".join(re.escape(n) for n in BEHANDELAAR_NAMEN) + r")\b", re.IGNORECASE)


def laad_config() -> configparser.SectionProxy:
    if not CONFIG_PATH.exists():
        raise SystemExit(f"Configbestand ontbreekt: {CONFIG_PATH}. Kopieer config.example.ini naar config.ini.")

    parser = configparser.ConfigParser()
    parser.read(CONFIG_PATH, encoding="utf-8")
    return parser["intake"]


def zet_logging_op(niveau: str) -> None:
    logging.basicConfig(
        level=getattr(logging, niveau.upper(), logging.INFO),
        format="%(asctime)s %(levelname)s %(message)s",
        handlers=[
            logging.FileHandler(LOG_PATH, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def sender_smtp_adres(mail) -> str:
    """Haalt het SMTP-adres van de afzender op. Bij Exchange-accounts geeft SenderEmailAddress vaak
    een X500/legacyExchangeDN i.p.v. een e-mailadres, dus eerst de betrouwbaardere routes proberen."""
    try:
        accessor = mail.PropertyAccessor
        smtp = accessor.GetProperty("http://schemas.microsoft.com/mapi/proptag/0x5D01001F")
        if smtp:
            return str(smtp)
    except Exception:
        pass

    try:
        if mail.SenderEmailType == "EX":
            exchange_user = mail.Sender.GetExchangeUser()
            if exchange_user is not None:
                return exchange_user.PrimarySmtpAddress
    except Exception:
        pass

    return mail.SenderEmailAddress or ""


def normaliseer_body(body: str) -> str:
    """Ontdoet de mailtekst van overtollige spaties/lege regels (Outlook-signatures staan er vol mee)."""
    regels = [re.sub(r"[ \t]+", " ", regel).rstrip() for regel in body.splitlines()]
    body = "\n".join(regels)
    return re.sub(r"\n{3,}", "\n\n", body).strip()


def vind_behandelaar_hint(*teksten: str) -> str:
    """Geeft de eerst gevonden naam uit BEHANDELAAR_NAMEN terug die in de teksten voorkomt, anders ''."""
    for tekst in teksten:
        match = BEHANDELAAR_RE.search(tekst)
        if match:
            return match.group(1)
    return ""


def vind_postvak_in(namespace, mailbox_naam: str, folder_naam: str):
    for folder in namespace.Folders:
        if folder.Name.strip().lower() == mailbox_naam.strip().lower():
            return folder.Folders[folder_naam]
    beschikbaar = ", ".join(f'"{folder.Name}"' for folder in namespace.Folders)
    raise SystemExit(
        f'Mailbox "{mailbox_naam}" niet gevonden onder de geopende Outlook-profielen.\n'
        f'Beschikbare mailboxen/postbussen in Outlook: {beschikbaar}\n'
        f'Zet de exacte naam hierboven als mailbox_naam in config.ini.'
    )


def post_eindgebruiker_mail(base_url: str, api_key: str, afzender: str, titel: str, body: str, behandelaar_hint: str) -> dict:
    resp = requests.post(
        f"{base_url}/api/tickets/vanuit-email",
        headers={"X-Api-Key": api_key},
        data={"afzender": afzender, "titel": titel, "omschrijving": body, "behandelaar_hint": behandelaar_hint},
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def post_mailmind_import(base_url: str, api_key: str, mail, afzender: str, onderwerp: str, body: str) -> dict:
    """Stuurt dezelfde e-mail ook naar de e-mail-/kennisbankverwerkingspipeline (MailMind), zodat elke
    ticketmelding ook meetelt voor logboekregistratie, AI-analyse en kennisbankopbouw
    (App\\Modules\\EmailVerwerking\\EmailImportController). Los van de ticketaanmaak hierboven: een
    mislukking hier mag de ticketflow nooit blokkeren (zie de aanroep in verwerk_mail())."""
    resp = requests.post(
        f"{base_url}/api/email-import/inbound",
        headers={"X-Api-Key": api_key},
        data={
            "bron_message_id": mail.EntryID,
            "afzender_email": afzender,
            "afzender_naam": mail.SenderName or "",
            "onderwerp": onderwerp,
            "body_ruw": mail.Body or "",
            "body_schoon": body,
            "ontvangen_op": mail.ReceivedTime.isoformat(),
        },
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def post_aca_update(base_url: str, api_key: str, match: re.Match, body: str, behandelaar_hint: str) -> dict:
    resp = requests.post(
        f"{base_url}/api/tickets/vanuit-aca-email",
        headers={"X-Api-Key": api_key},
        data={
            "cas_nummer": match.group("cas"),
            "aca_nummer": match.group("aca"),
            "actie": match.group("actie"),
            "titel": match.group("titel").strip(),
            "omschrijving": body,
            "behandelaar_hint": behandelaar_hint,
        },
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def verwerk_mail(mail, config: configparser.SectionProxy) -> bool:
    """Verwerkt één mail. Geeft True terug als de mail als verwerkt (gelezen) gezet mag worden."""
    afzender = sender_smtp_adres(mail)
    onderwerp = (mail.Subject or "").strip()
    body = normaliseer_body(mail.Body or "")
    behandelaar_hint = vind_behandelaar_hint(body, onderwerp)

    base_url = config["api_base_url"].rstrip("/")
    api_key = config["api_key"]
    aca_afzender = config["aca_sender_email"].strip().lower()

    if afzender.strip().lower() == aca_afzender:
        match = ACA_SUBJECT_RE.match(onderwerp)
        if not match:
            logging.warning('ACA-mail met onherkenbaar onderwerp, overgeslagen (blijft ongelezen): "%s"', onderwerp)
            return False

        resultaat = post_aca_update(base_url, api_key, match, body, behandelaar_hint)
        logging.info("ACA-update verwerkt (%s): %s", match.group("cas"), resultaat)
        return True

    resultaat = post_eindgebruiker_mail(base_url, api_key, afzender, onderwerp, body, behandelaar_hint)
    logging.info('Eindgebruiker-mail verwerkt ("%s" van %s): %s', onderwerp, afzender, resultaat)

    # Best-effort: telt ook mee voor de e-mail-/kennisbankverwerkingspipeline (MailMind). Een fout hier
    # mag de ticketaanmaak hierboven nooit blokkeren of de mail alsnog ongelezen laten — vandaar een
    # eigen try/except in plaats van de fout door te laten lopen naar de aanroeper van verwerk_mail().
    try:
        mailmind_resultaat = post_mailmind_import(base_url, api_key, mail, afzender, onderwerp, body)
        logging.info("MailMind-import verwerkt: %s", mailmind_resultaat)
    except Exception:
        logging.exception('MailMind-import mislukt voor "%s" (ticket is wel aangemaakt) — genegeerd.', onderwerp)

    return True


def main() -> None:
    config = laad_config()
    zet_logging_op(config.get("log_niveau", "INFO"))

    outlook = win32com.client.Dispatch("Outlook.Application")
    namespace = outlook.GetNamespace("MAPI")
    inbox = vind_postvak_in(namespace, config["mailbox_naam"], config.get("postvak_naam", "Inbox"))

    ongelezen = inbox.Items.Restrict("[Unread] = True")
    logging.info("%d ongelezen mail(s) gevonden.", ongelezen.Count)

    # Van achteraan doorlopen: Restrict-resultaten verschuiven van index zodra een item wijzigt.
    for i in range(ongelezen.Count, 0, -1):
        mail = ongelezen.Item(i)
        if mail.Class != OL_MAIL_ITEM_CLASS:
            continue

        try:
            if verwerk_mail(mail, config):
                mail.Unread = False
                mail.Save()
        except Exception:
            logging.exception('Verwerken van mail "%s" mislukt, blijft ongelezen voor volgende run.', mail.Subject)


if __name__ == "__main__":
    main()
