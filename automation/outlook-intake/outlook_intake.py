"""
Leest ongelezen mail uit de gedeelde mailbox IT@vhe.nl (via Outlook Classic/pywin32) en zet ze om
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


def vind_postvak_in(namespace, mailbox_naam: str, folder_naam: str):
    for folder in namespace.Folders:
        if folder.Name.strip().lower() == mailbox_naam.strip().lower():
            return folder.Folders[folder_naam]
    raise SystemExit(f'Mailbox "{mailbox_naam}" niet gevonden onder de geopende Outlook-profielen.')


def post_eindgebruiker_mail(base_url: str, api_key: str, afzender: str, titel: str, body: str) -> dict:
    resp = requests.post(
        f"{base_url}/api/tickets/vanuit-email",
        headers={"X-Api-Key": api_key},
        data={"afzender": afzender, "titel": titel, "omschrijving": body},
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def post_aca_update(base_url: str, api_key: str, match: re.Match, body: str) -> dict:
    resp = requests.post(
        f"{base_url}/api/tickets/vanuit-aca-email",
        headers={"X-Api-Key": api_key},
        data={
            "cas_nummer": match.group("cas"),
            "aca_nummer": match.group("aca"),
            "actie": match.group("actie"),
            "titel": match.group("titel").strip(),
            "omschrijving": body,
        },
        timeout=30,
    )
    resp.raise_for_status()
    return resp.json()


def verwerk_mail(mail, config: configparser.SectionProxy) -> bool:
    """Verwerkt één mail. Geeft True terug als de mail als verwerkt (gelezen) gezet mag worden."""
    afzender = sender_smtp_adres(mail)
    onderwerp = (mail.Subject or "").strip()
    body = (mail.Body or "").strip()

    base_url = config["api_base_url"].rstrip("/")
    api_key = config["api_key"]
    aca_afzender = config["aca_sender_email"].strip().lower()

    if afzender.strip().lower() == aca_afzender:
        match = ACA_SUBJECT_RE.match(onderwerp)
        if not match:
            logging.warning('ACA-mail met onherkenbaar onderwerp, overgeslagen (blijft ongelezen): "%s"', onderwerp)
            return False

        resultaat = post_aca_update(base_url, api_key, match, body)
        logging.info("ACA-update verwerkt (%s): %s", match.group("cas"), resultaat)
        return True

    resultaat = post_eindgebruiker_mail(base_url, api_key, afzender, onderwerp, body)
    logging.info('Eindgebruiker-mail verwerkt ("%s" van %s): %s', onderwerp, afzender, resultaat)
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
