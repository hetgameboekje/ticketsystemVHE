<?php

namespace App\Core;

/**
 * Eén CSRF-token per sessie (niet per formulier) — blijft geldig zolang de sessie leeft, dus
 * meerdere tabbladen/formulieren tegelijk open hebben werkt zonder token-mismatches.
 */
class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function verify(?string $token): bool
    {
        return is_string($token) && $token !== '' && hash_equals(self::token(), $token);
    }
}
