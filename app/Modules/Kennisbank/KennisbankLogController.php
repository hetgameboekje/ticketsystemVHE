<?php

namespace App\Modules\Kennisbank;

use App\Core\Controller;
use App\Modules\Kennisbank\Models\KennisbankLogModel;
use App\Modules\Kennisbank\Models\KennisbankModel;

class KennisbankLogController extends Controller
{
    public function store(int $artikelId): void
    {
        $this->requirePermission('kennisbank', 'schrijven');

        if (KennisbankModel::find($artikelId) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');

        if ($titel !== '' && $omschrijving !== '') {
            KennisbankLogModel::create([
                'kennisbank_artikel_id' => $artikelId,
                'user_id' => $this->currentUserId(),
                'titel' => $titel,
                'omschrijving' => $omschrijving,
            ]);
        }

        $this->redirect("/kennisbank/{$artikelId}");
    }
}
