<?php

namespace App\Modules\Verbeterpunt;

use App\Core\Controller;
use App\Modules\Verbeterpunt\Models\VerbeterpuntModel;
use App\Modules\Verbeterpunt\Models\VerbeterpuntTijdModel;

class VerbeterpuntTijdController extends Controller
{
    private const TOEGESTANE_BLOKKEN = [5, 10, 15, 30, 45, 60];

    public function store(int $verbeterpuntId): void
    {
        $this->requirePermission('verbeterpunten', 'schrijven');

        if (VerbeterpuntModel::find($verbeterpuntId) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $minuten = (int) ($_POST['minuten'] ?? 0);

        if (in_array($minuten, self::TOEGESTANE_BLOKKEN, true)) {
            VerbeterpuntTijdModel::create([
                'verbeterpunt_id' => $verbeterpuntId,
                'user_id' => $this->currentUserId(),
                'minuten' => $minuten,
            ]);
        }

        $this->redirect("/verbeterpunten/{$verbeterpuntId}");
    }
}
