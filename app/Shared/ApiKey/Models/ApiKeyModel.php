<?php

namespace App\Shared\ApiKey\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * Machine-to-machine API-sleutels voor externe scripts (bv. de Outlook-intake op it@vhe.nl of een
 * Taakplanner-taak), beheerd via Beheer > API-sleutels i.p.v. één gedeeld geheim in .env. Elke
 * sleutel heeft expliciete scopes (welke endpoints hij mag aanroepen) en kan los ingetrokken worden.
 */
class ApiKeyModel extends Model
{
    protected static string $table = 'api_keys';
    protected static array $fillable = [
        'naam', 'key_hash', 'key_prefix', 'scopes', 'aangemaakt_door_id', 'laatst_gebruikt_at',
    ];
    protected static bool $softDeletes = true;

    /** Scopes die via het beheerscherm toegekend kunnen worden, gekoppeld aan de endpoints die ze ontgrendelen. */
    public const SCOPES = [
        'ticket_intake' => 'E-mailintake tickets (eindgebruikers/key-users)',
        'aca_intake' => 'ACA-case-updates',
        'email_queue' => 'E-mailwachtrij verwerken (Taakplanner)',
        'ticket_herinneringen' => 'Ticketherinneringen genereren (Taakplanner)',
        'database_export' => 'Database-export (dev-sync: live database ophalen naar lokale omgeving)',
        'log_opschonen' => 'Oude logregels opschonen (Taakplanner)',
        'email_import' => 'E-mailimport voor E-mail & kennisbank verwerking (MailMind-intake)',
        'email_analysis' => 'AI-analyse en conceptartikelen verwerken (Taakplanner)',
    ];

    /** @return array{id:int,plaintext:string} */
    public static function generate(string $naam, array $scopes, ?int $aangemaaktDoorId): array
    {
        $plaintext = bin2hex(random_bytes(32));

        $id = self::create([
            'naam' => $naam,
            'key_hash' => hash('sha256', $plaintext),
            'key_prefix' => substr($plaintext, 0, 8),
            'scopes' => implode(',', $scopes),
            'aangemaakt_door_id' => $aangemaaktDoorId,
        ]);

        return ['id' => $id, 'plaintext' => $plaintext];
    }

    /** Vindt een actieve, niet-ingetrokken sleutel met de gevraagde scope; registreert direct het gebruik. */
    public static function vindActieveMetScope(string $plainKey, string $scope): ?array
    {
        if ($plainKey === '') {
            return null;
        }

        $stmt = Database::pdo()->prepare('SELECT * FROM api_keys WHERE key_hash = ? AND deleted_at IS NULL');
        $stmt->execute([hash('sha256', $plainKey)]);
        $row = $stmt->fetch();

        if ($row === false || !in_array($scope, explode(',', $row['scopes']), true)) {
            return null;
        }

        self::registreerGebruik((int) $row['id']);

        return $row;
    }

    public static function registreerGebruik(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE api_keys SET laatst_gebruikt_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function allGedeactiveerd(string $orderBy = 'naam ASC'): array
    {
        $stmt = Database::pdo()->query("SELECT * FROM api_keys WHERE deleted_at IS NOT NULL ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    /** Zoals find(), maar vindt ook ingetrokken sleutels — nodig om ze te kunnen heractiveren. */
    public static function findIncludingDeleted(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM api_keys WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
