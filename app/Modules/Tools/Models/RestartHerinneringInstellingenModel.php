<?php

namespace App\Modules\Tools\Models;

use App\Core\Database;

/**
 * Eén-rij instellingentabel voor de herstart-herinneringsmail (onderwerp/inhoud/cc/bcc), configureerbaar
 * vanuit Tools i.p.v. hardcoded in de controller. Placeholders in de inhoud: {naam}, {apparaat}, {dagen}.
 */
class RestartHerinneringInstellingenModel
{
    private const STANDAARD_INHOUD = <<<HTML
    <p>Beste {naam},</p>
    <p>Je apparaat "{apparaat}" is al {dagen} dagen niet herstart. Wil je deze week even herstarten?</p>
    HTML;

    public static function ophalen(): array
    {
        $stmt = Database::pdo()->query('SELECT * FROM herstart_herinnering_instellingen ORDER BY id ASC LIMIT 1');
        $row = $stmt->fetch();

        if ($row !== false) {
            return $row;
        }

        return [
            'id' => null,
            'onderwerp' => 'Even je apparaat herstarten?',
            'inhoud' => self::STANDAARD_INHOUD,
            'cc' => null,
            'bcc' => null,
        ];
    }

    public static function opslaan(string $onderwerp, string $inhoud, ?string $cc, ?string $bcc): void
    {
        $bestaand = Database::pdo()->query('SELECT id FROM herstart_herinnering_instellingen ORDER BY id ASC LIMIT 1')->fetch();

        if ($bestaand === false) {
            $stmt = Database::pdo()->prepare(
                'INSERT INTO herstart_herinnering_instellingen (onderwerp, inhoud, cc, bcc) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$onderwerp, $inhoud, $cc, $bcc]);
            return;
        }

        $stmt = Database::pdo()->prepare(
            'UPDATE herstart_herinnering_instellingen SET onderwerp = ?, inhoud = ?, cc = ?, bcc = ? WHERE id = ?'
        );
        $stmt->execute([$onderwerp, $inhoud, $cc, $bcc, $bestaand['id']]);
    }

    /** @return string[] */
    public static function adressenUitVeld(?string $veld): array
    {
        if ($veld === null || trim($veld) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $veld))));
    }
}
