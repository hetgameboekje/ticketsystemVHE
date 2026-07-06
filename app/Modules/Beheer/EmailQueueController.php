<?php

namespace App\Modules\Beheer;

use App\Core\Controller;
use App\Shared\Mail\Models\EmailQueueModel;

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
}
