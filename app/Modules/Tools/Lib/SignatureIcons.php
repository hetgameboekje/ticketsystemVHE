<?php

namespace App\Modules\Tools\Lib;

/**
 * Vaste iconenlijst die de handtekening-editor aan een regel kan koppelen. De bestanden
 * staan onder /assets/img/signature-icons/ zodat dezelfde URL zowel in de preview als in
 * de geëxporteerde HTML (geplakt in een mailclient) werkt.
 */
final class SignatureIcons
{
    public const ICONS = [
        'pin' => ['label' => 'Adres', 'file' => 'pin.svg'],
        'phone' => ['label' => 'Telefoon', 'file' => 'phone.svg'],
        'mobile' => ['label' => 'Mobiel', 'file' => 'mobile.svg'],
        'email' => ['label' => 'E-mail', 'file' => 'email.svg'],
        'globe' => ['label' => 'Website', 'file' => 'globe.svg'],
        'fax' => ['label' => 'Fax', 'file' => 'fax.svg'],
        'linkedin' => ['label' => 'LinkedIn', 'file' => 'linkedin.svg'],
    ];

    public static function url(string $key): string
    {
        $icon = self::ICONS[$key] ?? null;

        return $icon ? '/assets/img/signature-icons/' . $icon['file'] : '';
    }
}
