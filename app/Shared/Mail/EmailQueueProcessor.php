<?php

namespace App\Shared\Mail;

use App\Core\Mailer;
use App\Shared\Mail\Models\EmailQueueModel;

/**
 * Verwerkt de e-mailwachtrij (zie EmailQueueModel). Bedoeld om periodiek aangeroepen te worden
 * door een externe scheduler via POST /api/email-queue/verwerken.
 */
class EmailQueueProcessor
{
    private const MAX_PER_RUN = 10;

    /** @return array{verzonden:int,fout:int,overbelast:bool,aantal_openstaand:int} */
    public static function verwerk(): array
    {
        $openstaand = EmailQueueModel::openstaand();

        // Meer dan 10 openstaand duidt op iets abnormaals (verzendjob die al een tijd niet gedraaid
        // heeft, of een producent die te veel mails aanmaakt) — in dat geval NIET alsnog alles
        // versturen, maar direct (buiten de wachtrij om) één waarschuwing naar de beheerder sturen.
        if (count($openstaand) > self::MAX_PER_RUN) {
            self::waarschuwOverbelasting(count($openstaand));
            return ['verzonden' => 0, 'fout' => 0, 'overbelast' => true, 'aantal_openstaand' => count($openstaand)];
        }

        $verzonden = 0;
        $fout = 0;

        foreach ($openstaand as $mail) {
            try {
                Mailer::verstuur($mail['ontvanger'], $mail['onderwerp'], $mail['inhoud']);
                EmailQueueModel::markVerzonden((int) $mail['id']);
                $verzonden++;
            } catch (\Throwable $e) {
                EmailQueueModel::markFout((int) $mail['id'], $e->getMessage());
                $fout++;
            }
        }

        return ['verzonden' => $verzonden, 'fout' => $fout, 'overbelast' => false, 'aantal_openstaand' => count($openstaand)];
    }

    private static function waarschuwOverbelasting(int $aantal): void
    {
        $config = require APP_ROOT . '/config/config.php';
        $admin = $config['mail']['admin_address'];

        if ($admin === '') {
            return;
        }

        try {
            Mailer::verstuur(
                $admin,
                'Ticketsysteem Leen van Punt: te veel e-mails in de wachtrij',
                "Er staan {$aantal} e-mails klaar om verzonden te worden (limiet is " . self::MAX_PER_RUN . " per verwerking).<br>"
                . 'De wachtrij wordt niet automatisch verwerkt totdat dit is opgelost. Bekijk Beheer &gt; E-mails.'
            );
        } catch (\Throwable $e) {
            // Als zelfs de waarschuwing niet verstuurd kan worden, is er niets meer te doen vanuit hier.
        }
    }
}
