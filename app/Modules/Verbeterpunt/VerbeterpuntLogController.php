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

        $opmerking = trim($_POST['opmerking'] ?? '');
        $nieuweStatus = $_POST['status'] ?? '';
        $statusGewijzigd = $nieuweStatus !== '' && $nieuweStatus !== $verbeterpunt['status'];

        if ($opmerking !== '' || $statusGewijzigd) {
            VerbeterpuntLogModel::create([
                'verbeterpunt_id' => $verbeterpuntId,
                'user_id' => $this->currentUserId(),
                'opmerking' => $opmerking !== '' ? $opmerking : 'Status bijgewerkt.',
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
