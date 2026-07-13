<?php

namespace App\Modules\Medewerker;

class MedewerkerImport
{
    /**
     * Parseert een gebruikers-export CSV (kolommen "User name", "Email", "User access",
     * "Phone number", "Assigned devices"). Slaat niets op — de controller bepaalt per rij of
     * een bestaande medewerker (via e-mail) bijgewerkt wordt of een nieuwe aangemaakt wordt, en
     * probeert de hostnamen uit "Assigned devices" te koppelen aan bestaande apparaten.
     *
     * @return array<int, array{voornaam: string, achternaam: string, email: string, telefoon: string, status: string, hostnames: array<int, string>}>
     */
    public static function parse(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Kan het CSV-bestand niet lezen.');
        }

        $headerLine = fgetcsv($handle);
        if ($headerLine === false || $headerLine === null) {
            fclose($handle);
            throw new \RuntimeException('Het CSV-bestand is leeg.');
        }

        // Exports beginnen vaak met een UTF-8 BOM, die anders aan de eerste kolomnaam blijft plakken.
        $headerLine[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine[0]);
        $col = array_flip(array_map('strtolower', $headerLine));

        $get = static function (array $row, string $header) use ($col): string {
            $i = $col[strtolower($header)] ?? null;
            return $i !== null ? trim((string) ($row[$i] ?? '')) : '';
        };

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $email = $get($row, 'Email');
            if ($email === '') {
                continue;
            }

            [$voornaam, $achternaam] = self::splitsNaam($get($row, 'User name'));

            $rows[] = [
                'voornaam' => $voornaam,
                'achternaam' => $achternaam,
                'email' => $email,
                // Excel-exports zetten vaak een ' voor telefoonnummers om ze als tekst te forceren.
                'telefoon' => ltrim($get($row, 'Phone number'), "'"),
                'status' => strtolower($get($row, 'User access')) === 'enabled' ? 'actief' : 'inactief',
                'hostnames' => self::parseHostnames($get($row, 'Assigned devices')),
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \RuntimeException('Geen rijen met e-mailadres gevonden in dit CSV-bestand.');
        }

        return $rows;
    }

    /** @return array{0: string, 1: string} */
    private static function splitsNaam(string $naam): array
    {
        $naam = trim($naam);
        if ($naam === '') {
            return ['', ''];
        }

        $delen = explode(' ', $naam, 2);
        return [$delen[0], $delen[1] ?? ''];
    }

    /** @return array<int, string> */
    private static function parseHostnames(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $hostnames = [];
        foreach (explode(',', $raw) as $entry) {
            $hostnaam = trim((string) preg_replace('/\s*\([^)]*\)\s*$/', '', trim($entry)));
            if ($hostnaam !== '') {
                $hostnames[] = $hostnaam;
            }
        }

        return $hostnames;
    }
}
