<?php

namespace App\Modules\Verbeterpunt;

use App\Core\Controller;
use App\Modules\Verbeterpunt\Models\VerbeterpuntLogModel;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;

class VerbeterpuntLogController extends Controller
{
    public function store(int $verbeterpuntId): void
    {
        $this->requirePermission('verbeterpunten', 'schrijven');

        $verbeterpunt = VerbeterpuntModel::find($verbeterpuntId);
        if ($verbeterpunt === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $titel = trim($_POST['titel'] ?? '');
        $opmerking = trim($_POST['opmerking'] ?? '');
        // Een opmerking telt alleen mee als titel én tekst zijn ingevuld (zelfde gedrag als Ticket).
        $opmerkingGeldig = $titel !== '' && $opmerking !== '';
        $nieuweStatus = $_POST['status'] ?? '';
        $statusGewijzigd = $nieuweStatus !== '' && $nieuweStatus !== $verbeterpunt['status'];

        if ($opmerkingGeldig || $statusGewijzigd) {
            VerbeterpuntLogModel::create([
                'verbeterpunt_id' => $verbeterpuntId,
                'user_id' => $this->currentUserId(),
                'titel' => $opmerkingGeldig ? $titel : null,
                'opmerking' => $opmerkingGeldig ? $opmerking : 'Status bijgewerkt.',
                'status_van' => $statusGewijzigd ? $verbeterpunt['status'] : null,
                'status_naar' => $statusGewijzigd ? $nieuweStatus : null,
            ]);
        }

        if ($statusGewijzigd) {
            VerbeterpuntModel::update($verbeterpuntId, ['status' => $nieuweStatus]);
        }

        $this->redirect("/verbeterpunten/{$verbeterpuntId}");
    }
}
