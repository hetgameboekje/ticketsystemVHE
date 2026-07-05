<?php

namespace App\Modules\CyberRisico;

use App\Core\Controller;
use App\Modules\CyberRisico\Models\CyberRisicoLogModel;
use App\Modules\CyberRisico\Models\CyberRisicoModel;

class CyberRisicoLogController extends Controller
{
    public function store(int $cyberrisicoId): void
    {
        $this->requirePermission('cyberrisicos', 'schrijven');

        if (CyberRisicoModel::find($cyberrisicoId) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');

        if ($titel !== '' && $omschrijving !== '') {
            CyberRisicoLogModel::create([
                'cyberrisico_id' => $cyberrisicoId,
                'user_id' => $this->currentUserId(),
                'titel' => $titel,
                'omschrijving' => $omschrijving,
            ]);
        }

        $this->redirect("/cyberrisicos/{$cyberrisicoId}");
    }
}
