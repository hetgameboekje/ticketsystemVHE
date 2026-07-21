<?php

namespace App\Modules\Voorraad;

class DxDiagParser
{
    /** Bewust beperkt tot wat voor een voorraaditem relevant is (user: "beknopt"). */
    private const SYSTEEM_LABELS = [
        'Machine name', 'Operating System', 'System Manufacturer', 'System Model',
        'BIOS', 'Processor', 'Memory', 'DirectX Version',
    ];

    private const VIDEOKAART_LABELS = [
        'Card name', 'Manufacturer', 'Chip type', 'Display Memory', 'Dedicated Memory', 'Driver Version',
    ];

    /** @return array{systeem: array<string, string>, videokaarten: array<int, array<string, string>>} */
    public static function parse(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            throw new \RuntimeException('Kan het DxDiag-bestand niet lezen.');
        }

        $content = str_replace("\r\n", "\n", $content);
        $sections = self::splitSections($content);

        $systeem = isset($sections['System Information'])
            ? self::parseKeyValues($sections['System Information'], self::SYSTEEM_LABELS)
            : [];

        $videokaarten = [];
        if (isset($sections['Display Devices'])) {
            foreach (self::splitVideokaarten($sections['Display Devices']) as $block) {
                $kaart = self::parseKeyValues($block, self::VIDEOKAART_LABELS);
                if ($kaart !== []) {
                    $videokaarten[] = $kaart;
                }
            }
        }

        if ($systeem === [] && $videokaarten === []) {
            throw new \RuntimeException('Geen "System Information" of "Display Devices" gevonden — is dit een geldig DxDiag-rapport (.txt)?');
        }

        return ['systeem' => $systeem, 'videokaarten' => $videokaarten];
    }

    /**
     * Splitst een DxDiag-export op de "------\n<Header>\n------\n"-sectiekoppen.
     *
     * @return array<string, string> header => sectie-inhoud
     */
    private static function splitSections(string $content): array
    {
        preg_match_all('/^-{10,}\n(.+?)\n-{10,}\n/m', $content, $matches, PREG_OFFSET_CAPTURE);

        $sections = [];
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $header = trim($matches[1][$i][0]);
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($content);
            $sections[$header] = substr($content, $start, $end - $start);
        }

        return $sections;
    }

    /** Eén "Display Devices"-sectie kan meerdere kaarten bevatten, elk beginnend met "Card name:". */
    private static function splitVideokaarten(string $section): array
    {
        $parts = preg_split('/(?=^\s*Card name\s*:)/m', $section) ?: [];
        return array_values(array_filter($parts, fn (string $p) => trim($p) !== ''));
    }

    /** @param array<int, string> $labels @return array<string, string> */
    private static function parseKeyValues(string $block, array $labels): array
    {
        $result = [];
        foreach ($labels as $label) {
            if (preg_match('/^\s*' . preg_quote($label, '/') . '\s*:\s?(.*)$/mi', $block, $m)) {
                $value = trim($m[1]);
                if ($value !== '') {
                    $result[$label] = $value;
                }
            }
        }

        return $result;
    }
}
