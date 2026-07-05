<?php

namespace App\Modules\Reflectie;

use App\Core\Controller;
use App\Modules\Reflectie\Models\ReflectieLogModel;
use App\Modules\Reflectie\Models\ReflectieModel;

class ReflectieLogController extends Controller
{
    public function store(int $reflectieId): void
    {
        $this->requirePermission('reflecties', 'schrijven');

        if (ReflectieModel::find($reflectieId) === null) {
            http_response_code(404);
            echo 'Niet gevonden.';
            return;
        }

        $titel = trim($_POST['titel'] ?? '');
        $omschrijving = trim($_POST['omschrijving'] ?? '');

        if ($titel !== '' && $omschrijving !== '') {
            ReflectieLogModel::create([
                'reflectie_id' => $reflectieId,
                'user_id' => $this->currentUserId(),
                'titel' => $titel,
                'omschrijving' => $omschrijving,
            ]);
        }

        $this->redirect("/reflecties/{$reflectieId}");
    }
}
