<?php

namespace App\Shared\Log;

use App\Shared\Log\Models\PaginaBezoekModel;

/** Registreert wie (gebruiker + IP) welke pagina heeft bezocht, voor het beheer-logboek. */
class PaginaBezoekLogger
{
    /** Velden die nooit in het logboek terecht mogen komen, ook al staan ze in $_GET/$_POST. */
    private const GEVOELIGE_VELDEN = ['wachtwoord', 'wachtwoord_herhaal', 'nieuw_wachtwoord', 'password'];

    public static function log(string $method, string $url): void
    {
        try {
            PaginaBezoekModel::create([
                'user_id' => $_SESSION['user']['id'] ?? null,
                'ip_adres' => $_SERVER['REMOTE_ADDR'] ?? '',
                'methode' => $method,
                'url' => $url,
                'parameters' => self::parametersJson(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Logging mag de normale afhandeling van de request nooit breken.
        }
    }

    private static function parametersJson(): ?string
    {
        $params = array_merge($_GET, $_POST);
        if (empty($params)) {
            return null;
        }

        foreach (self::GEVOELIGE_VELDEN as $veld) {
            if (array_key_exists($veld, $params)) {
                $params[$veld] = '••••••••';
            }
        }

        return json_encode($params, JSON_UNESCAPED_UNICODE);
    }
}
