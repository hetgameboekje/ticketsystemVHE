<?php

namespace App\Modules\Tools\Lib;

/**
 * Bouwt een vCard 3.0 (.vcf)-body uit telefoonlijst-rijen. Rijen zijn geïndexeerd op
 * kleine-letter-kopnaam (zie App\Core\Xlsx::readSheet) — kolomnamen komen uit het
 * bronbestand ("Naam", "Functie", "Afdeling", "Toestel", "GSM Nummer"), dus elke kolom
 * mag ontbreken zonder dat de conversie stukloopt.
 */
final class VCardGenerator
{
    /** @param array<int, array<string, string>> $rows */
    public static function build(array $rows): string
    {
        $cards = '';

        foreach ($rows as $row) {
            $name = trim($row['naam'] ?? '');
            if ($name === '') {
                continue;
            }

            [$lastName, $firstName] = self::splitName($name);
            $fullName = trim($firstName . ' ' . $lastName) ?: $name;

            $title = trim($row['functie'] ?? '');
            $org = trim($row['afdeling'] ?? '');
            $extension = trim($row['toestel'] ?? '');
            $mobile = trim($row['gsm nummer'] ?? '');

            $lines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                'N:' . self::escape($lastName) . ';' . self::escape($firstName) . ';;;',
                'FN:' . self::escape($fullName),
            ];

            if ($org !== '') {
                $lines[] = 'ORG:' . self::escape($org);
            }
            if ($title !== '') {
                $lines[] = 'TITLE:' . self::escape($title);
            }
            if ($extension !== '') {
                $lines[] = 'TEL;TYPE=WORK,VOICE:' . self::escape($extension);
            }
            if ($mobile !== '') {
                $lines[] = 'TEL;TYPE=CELL:' . self::escape($mobile);
            }

            $lines[] = 'END:VCARD';

            $cards .= implode("\r\n", $lines) . "\r\n";
        }

        return $cards;
    }

    public static function countCards(string $vcf): int
    {
        return substr_count($vcf, 'BEGIN:VCARD');
    }

    /** @return array{0: string, 1: string} [achternaam, voornaam] */
    private static function splitName(string $name): array
    {
        if (str_contains($name, ',')) {
            [$last, $first] = array_map('trim', explode(',', $name, 2));

            return [$last, $first];
        }

        $parts = preg_split('/\s+/', $name) ?: [$name];
        $last = array_pop($parts);

        return [$last, implode(' ', $parts)];
    }

    private static function escape(string $value): string
    {
        return str_replace(["\\", ',', ';', "\n"], ["\\\\", '\,', '\;', '\n'], $value);
    }
}
