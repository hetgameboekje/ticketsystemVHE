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
            // Nieuwe opmerkingen komen achteraan; de volgorde daarna is verder alleen via
            // slepen (zie reorder()) aan te passen, niet meer via een handmatig getal.
            KennisbankLogModel::create([
                'kennisbank_artikel_id' => $artikelId,
                'user_id' => $this->currentUserId(),
                'titel' => $titel,
                'omschrijving' => $omschrijving,
                'volgorde' => count(KennisbankLogModel::forArtikel($artikelId)),
            ]);
        }

        $this->redirect("/kennisbank/{$artikelId}");
    }

    /**
     * AJAX-endpoint voor het slepen van opmerkingen (zie show.php): ontvangt de volledige,
     * nieuwe volgorde van log-id's voor dit artikel en slaat de positie van elk item op.
     */
    public function reorder(int $artikelId): void
    {
        $this->requirePermission('kennisbank', 'schrijven');
        header('Content-Type: application/json');

        if (KennisbankModel::find($artikelId) === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Artikel niet gevonden.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $order = is_array($payload['order'] ?? null) ? $payload['order'] : [];

        foreach ($order as $index => $logId) {
            KennisbankLogModel::updateVolgorde((int) $logId, $index);
        }

        echo json_encode(['status' => 'ok']);
    }
}
