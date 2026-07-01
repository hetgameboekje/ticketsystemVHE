<?php

namespace App\Core;

class Barcode
{
    // Code 128, subset B (printable ASCII 32-126). Elke rij: breedtes van 6 modules
    // (afwisselend streep/spatie, begint met streep), som is altijd 11. Waarde 106 (STOP)
    // heeft 7 modules die optellen tot 13. Index in deze array = symboolwaarde 0-106.
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312',
        '132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222',
        '123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131',
        '311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321',
        '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121',
        '313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321',
        '331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224',
        '111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114',
        '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112',
        '421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113',
        '114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412',
        '211214', '211232', '2331112',
    ];

    private const START_B = 104;
    private const STOP = 106;
    private const FIRST_CHAR = 32;

    public static function code128Svg(string $text, int $moduleWidth = 2, int $height = 60): string
    {
        $values = self::encode($text);
        $modules = self::toModules($values);

        $totalWidth = array_sum($modules) * $moduleWidth;
        $quietZone = $moduleWidth * 10;
        $svgWidth = $totalWidth + $quietZone * 2;
        $textHeight = 16;
        $svgHeight = $height + $textHeight;

        $bars = '';
        $x = $quietZone;
        $isBar = true;
        foreach ($modules as $moduleCount) {
            $w = $moduleCount * $moduleWidth;
            if ($isBar) {
                $bars .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $height . '" fill="#000"/>';
            }
            $x += $w;
            $isBar = !$isBar;
        }

        $label = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="' . $svgHeight . '" viewBox="0 0 ' . $svgWidth . ' ' . $svgHeight . '">'
            . '<rect x="0" y="0" width="' . $svgWidth . '" height="' . $svgHeight . '" fill="#fff"/>'
            . $bars
            . '<text x="' . ($svgWidth / 2) . '" y="' . ($height + 13) . '" text-anchor="middle" '
            . 'font-family="monospace" font-size="12">' . $label . '</text>'
            . '</svg>';
    }

    /** @return int[] Code128B symboolwaarden, inclusief start- en stopcode + checksum. */
    public static function encode(string $text): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('Barcode-tekst mag niet leeg zijn.');
        }

        $values = [self::START_B];
        $checksum = self::START_B;

        $chars = str_split($text);
        foreach ($chars as $i => $char) {
            $ord = ord($char);
            if ($ord < self::FIRST_CHAR || $ord > 126) {
                throw new \InvalidArgumentException("Karakter '{$char}' kan niet in Code128B gecodeerd worden.");
            }
            $value = $ord - self::FIRST_CHAR;
            $values[] = $value;
            $checksum += $value * ($i + 1);
        }

        $values[] = $checksum % 103;
        $values[] = self::STOP;

        return $values;
    }

    /** @param int[] $values @return int[] modulebreedtes (afwisselend streep/spatie, begint met streep) */
    private static function toModules(array $values): array
    {
        $modules = [];
        foreach ($values as $value) {
            foreach (str_split(self::PATTERNS[$value]) as $w) {
                $modules[] = (int) $w;
            }
        }

        return $modules;
    }

    /** Decodeert een eigen-gegenereerde waardenreeks terug naar tekst — gebruikt door de zelftest. */
    public static function decode(array $values): string
    {
        $reverse = array_flip(self::PATTERNS);
        $text = '';

        $data = array_slice($values, 1, -2);
        foreach ($data as $value) {
            $text .= chr($value + self::FIRST_CHAR);
        }

        return $text;
    }

    public static function selfTest(): array
    {
        $errors = [];

        foreach (self::PATTERNS as $i => $pattern) {
            $sum = array_sum(str_split($pattern));
            $expected = ($i === self::STOP) ? 13 : 11;
            if ($sum !== $expected) {
                $errors[] = "Patroon {$i} ('{$pattern}') telt op tot {$sum}, verwacht {$expected}.";
            }
        }

        if (count(self::PATTERNS) !== 107) {
            $errors[] = 'Verwacht 107 patronen (0-106), gevonden ' . count(self::PATTERNS) . '.';
        }

        foreach (['LAP-000123', 'HDM-3M', 'A1', 'PJJ123C'] as $sample) {
            $values = self::encode($sample);
            $decoded = self::decode($values);
            if ($decoded !== $sample) {
                $errors[] = "Round-trip mislukt voor '{$sample}': kreeg '{$decoded}' terug.";
            }
        }

        return $errors;
    }
}
