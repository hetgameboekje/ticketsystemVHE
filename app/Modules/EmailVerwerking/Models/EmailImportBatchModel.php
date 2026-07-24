<?php

namespace App\Modules\EmailVerwerking\Models;

use App\Core\Database;
use App\Core\Model;

/** Eén rij per run van het intake-script (bv. mailmind_intake.py), voor tellingen/diagnose per batch. */
class EmailImportBatchModel extends Model
{
    protected static string $table = 'email_import_batches';
    protected static array $fillable = [
        'bron', 'status', 'aantal_gevonden', 'aantal_verwerkt', 'aantal_mislukt', 'gestart_at', 'voltooid_at',
    ];

    public static function start(string $bron): int
    {
        return self::create(['bron' => $bron, 'status' => 'lopend']);
    }

    public static function voltooien(int $id, int $verwerkt, int $mislukt): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE email_import_batches SET status = ?, aantal_verwerkt = ?, aantal_mislukt = ?, voltooid_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$mislukt > 0 ? 'voltooid_met_fouten' : 'voltooid', $verwerkt, $mislukt, $id]);
    }
}
