<?php

namespace App\Shared\Log;

use App\Shared\Log\Models\PaginaBezoekModel;

/** Registreert wie (gebruiker + IP) welke pagina heeft bezocht, voor het beheer-logboek. */
class PaginaBezoekLogger
{
    public static function log(string $method, string $url): void
    {
        try {
            PaginaBezoekModel::create([
                'user_id' => $_SESSION['user']['id'] ?? null,
                'ip_adres' => $_SERVER['REMOTE_ADDR'] ?? '',
                'methode' => $method,
                'url' => $url,
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Logging mag de normale afhandeling van de request nooit breken.
        }
    }
}
