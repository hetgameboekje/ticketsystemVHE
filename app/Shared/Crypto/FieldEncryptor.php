<?php

namespace App\Shared\Crypto;

/**
 * Versleutelt/ontsleutelt losse databasevelden met AES-256-GCM. Sleutel komt uit de
 * omgevingsvariabele APP_ENCRYPTION_KEY (32 bytes, base64 — genereren met
 * `openssl rand -base64 32`; zie ook config('encryptionKey') in config/config.php).
 * Opslagformaat: base64(iv(12 bytes) . tag(16 bytes) . ciphertext).
 * Niet-deterministisch (random IV per aanroep) — velden die hiermee versleuteld zijn kunnen
 * dus niet met SQL WHERE/LIKE vergeleken worden, alleen na decrypt() in PHP.
 */
class FieldEncryptor
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12;
    private const TAG_LENGTH = 16;

    public static function encrypt(?string $plaintext): ?string
    {
        if ($plaintext === null) {
            return null;
        }

        $key = self::key();
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);

        if ($ciphertext === false) {
            throw new \RuntimeException('Versleutelen van veld is mislukt.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public static function decrypt(?string $encoded): ?string
    {
        if ($encoded === null || $encoded === '') {
            return $encoded;
        }

        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < self::IV_LENGTH + self::TAG_LENGTH) {
            return $encoded; // niet ons formaat (bv. nog niet-gemigreerde plaintext) — ongewijzigd teruggeven
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $tag = substr($raw, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH + self::TAG_LENGTH);

        $key = self::key();
        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag);

        return $plaintext === false ? $encoded : $plaintext;
    }

    private static function key(): string
    {
        $configured = getenv('APP_ENCRYPTION_KEY') ?: '';
        if ($configured === '') {
            throw new \RuntimeException(
                'APP_ENCRYPTION_KEY ontbreekt — zet die in .env (genereer met: openssl rand -base64 32).'
            );
        }

        $key = base64_decode($configured, true);
        if ($key === false || strlen($key) !== 32) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY moet een base64-encoded sleutel van 32 bytes zijn.');
        }

        return $key;
    }
}
