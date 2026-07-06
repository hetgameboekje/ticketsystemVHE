<?php

namespace App\Core;

/**
 * Minimale SMTP-client (geen Composer/PHPMailer, zelfde aanpak als Xlsx.php). Verstuurt één e-mail
 * per verbinding. Configuratie komt uit config/config.php 'mail' (zie .env: MAIL_HOST, MAIL_PORT,
 * MAIL_ENCRYPTION, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME).
 */
class Mailer
{
    public static function verstuur(string $naar, string $onderwerp, string $inhoudHtml): void
    {
        $config = self::config();

        if ($config['host'] === '') {
            throw new \RuntimeException('Geen mailserver geconfigureerd (MAIL_HOST ontbreekt in .env).');
        }

        $prefix = $config['encryption'] === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client(
            "{$prefix}{$config['host']}:{$config['port']}",
            $errno,
            $errstr,
            10
        );

        if ($socket === false) {
            throw new \RuntimeException("Kan geen verbinding maken met mailserver {$config['host']}:{$config['port']} — {$errstr}");
        }

        try {
            self::verwacht($socket, 220);
            $ehloNaam = $_SERVER['SERVER_NAME'] ?? 'localhost';
            self::commando($socket, "EHLO {$ehloNaam}", 250);

            if ($config['encryption'] === 'tls') {
                self::commando($socket, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS-onderhandeling met de mailserver is mislukt.');
                }
                self::commando($socket, "EHLO {$ehloNaam}", 250);
            }

            if ($config['username'] !== '') {
                self::commando($socket, 'AUTH LOGIN', 334);
                self::commando($socket, base64_encode($config['username']), 334);
                self::commando($socket, base64_encode($config['password']), 235);
            }

            self::commando($socket, "MAIL FROM:<{$config['from_address']}>", 250);
            self::commando($socket, "RCPT TO:<{$naar}>", 250);
            self::commando($socket, 'DATA', 354);

            $van = $config['from_name'] !== '' ? "{$config['from_name']} <{$config['from_address']}>" : $config['from_address'];
            $headers = [
                'From: ' . $van,
                'To: ' . $naar,
                'Subject: ' . $onderwerp,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
            ];

            // Regels die met een punt beginnen moeten volgens RFC 5321 "byte-stuffed" worden (dubbele punt),
            // anders interpreteert de SMTP-server een losse "." als het einde van de DATA-sectie.
            $body = str_replace("\n.", "\n..", $inhoudHtml);
            $bericht = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
            self::commando($socket, $bericht, 250);

            self::commando($socket, 'QUIT', 221);
        } finally {
            fclose($socket);
        }
    }

    /** @return array{host:string,port:int,encryption:string,username:string,password:string,from_address:string,from_name:string,admin_address:string} */
    private static function config(): array
    {
        $config = require APP_ROOT . '/config/config.php';
        return $config['mail'];
    }

    private static function commando($socket, string $regel, int $verwachteCode): string
    {
        fwrite($socket, $regel . "\r\n");
        return self::verwacht($socket, $verwachteCode);
    }

    private static function verwacht($socket, int $verwachteCode): string
    {
        $respons = '';
        while (($regel = fgets($socket, 515)) !== false) {
            $respons .= $regel;
            // Bij multi-line SMTP-responses ("250-...") is de laatste regel te herkennen aan een spatie i.p.v. streepje na de code.
            if (preg_match('/^\d{3} /', $regel)) {
                break;
            }
        }

        $code = (int) substr($respons, 0, 3);
        if ($code !== $verwachteCode) {
            throw new \RuntimeException("Onverwachte SMTP-respons (verwacht {$verwachteCode}): " . trim($respons));
        }

        return $respons;
    }
}
