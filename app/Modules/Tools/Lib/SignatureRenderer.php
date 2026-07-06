<?php

namespace App\Modules\Tools\Lib;

/**
 * Rendert de geordende regels van een handtekening naar kopieerbare HTML. Elke regel is
 * platte tekst of een icoon+tekst-regel, optioneel vet en/of in een link gewikkeld.
 */
final class SignatureRenderer
{
    private const FONT = "font-family:Tahoma,Arial,sans-serif;font-size:10pt;color:#242424;";
    private const LINK = "color:#0b95d3;text-decoration:none;";

    /** @param array<int, array<string, mixed>> $lines */
    public static function render(array $lines, string $baseUrl = ''): string
    {
        $html = '';

        foreach ($lines as $line) {
            $text = htmlspecialchars((string) ($line['text'] ?? ''), ENT_QUOTES);
            if ($text === '') {
                continue;
            }

            $weight = !empty($line['bold']) ? 'font-weight:bold;' : '';
            $inner = $text;

            $href = trim((string) ($line['href'] ?? ''));
            if ($href !== '') {
                $inner = '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '" style="' . self::LINK . $weight . '">' . $inner . '</a>';
            }

            if (($line['type'] ?? 'text') === 'icon') {
                $iconKey = (string) ($line['icon'] ?? '');
                $iconUrl = SignatureIcons::url($iconKey);
                $img = $iconUrl !== ''
                    ? '<img src="' . htmlspecialchars($baseUrl . $iconUrl, ENT_QUOTES) . '" width="14" height="14" alt="" style="display:block;">'
                    : '';

                $html .= '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 2px 0;">'
                    . '<tr><td style="padding-right:6px;vertical-align:middle;">' . $img . '</td>'
                    . '<td style="' . self::FONT . $weight . '">' . $inner . '</td></tr>'
                    . '</table>';

                continue;
            }

            $html .= '<p style="margin:0 0 2px 0;' . self::FONT . $weight . '">' . $inner . '</p>';
        }

        return $html;
    }
}
