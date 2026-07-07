<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Core\Mailer;
use App\Shared\Mail\Models\EmailQueueModel;
use App\Shared\User\Models\UserModel;

class EmailQueueController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $this->render('Modules/Beheer/Views/EmailQueueView/index', [
            'activeModule' => 'beheer',
            'pageTitle' => "E-mails",
            'emails' => EmailQueueModel::alle(),
        ]);
    }

    /**
     * Stuurt direct (buiten de wachtrij om, zoals EmailQueueProcessor::waarschuwOverbelasting())
     * een testmail naar het e-mailadres van de ingelogde beheerder, zodat de SMTP-configuratie
     * te controleren is zonder op de reguliere wachtrijverwerking te wachten.
     */
    public function test(): void
    {
        $this->requireAdmin();

        $user = UserModel::find((int) $this->currentUserId());
        $ontvanger = $user['email'] ?? '';

        if ($ontvanger === '') {
            $_SESSION['flash_error'] = 'Geen e-mailadres bekend voor jouw account.';
            $this->redirect('/beheer/emails');
        }

        $onderwerp = 'Ticketsysteem VHE: testmail';
        $inhoud = 'Dit is een testmail, verstuurd via Beheer &gt; E-mails op ' . date('d-m-Y H:i') . '.';

        try {
            Mailer::verstuur($ontvanger, $onderwerp, $inhoud);
            EmailQueueModel::voegToe($ontvanger, $onderwerp, $inhoud, isTest: true);
            $_SESSION['flash_success'] = "Testmail verzonden naar {$ontvanger}.";
        } catch (\Throwable $e) {
            // Niet gelogd in email_queue: er is niets verzonden, dus er is geen verzendhistorie om te tonen.
            $_SESSION['flash_error'] = 'Versturen van testmail is mislukt: ' . $e->getMessage();
        }

        $this->redirect('/beheer/emails');
    }
}
