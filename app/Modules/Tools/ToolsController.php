<?php

namespace App\Modules\Tools;

use App\Core\Controller;
use App\Modules\Tools\Models\PhonebookJobModel;

class ToolsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->render('Modules/Tools/Views/ToolsView/index', [
            'activeModule' => 'tools',
            'pageTitle' => 'Tools',
            'laatsteTelefoonlijst' => PhonebookJobModel::mostRecentDone(),
        ]);
    }
}
