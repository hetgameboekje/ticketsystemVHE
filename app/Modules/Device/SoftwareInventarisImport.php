<?php

namespace App\Modules\Device;

class SoftwareInventarisImport
{
    /**
     * Parseert een fleet-brede software-inventaris-export (Publisher, Name, Version, Platform,
     * System Component, Recent, First, Devices) — in tegenstelling tot DeviceSoftwareImport hoort
     * één rij hier niet bij één apparaat: de "Devices"-kolom bevat een lijst van alle apparaat-ID's
     * waarop die software staat (bv. "[65, 259, 51, 87]"). Deze import legt bewust geen koppeling
     * met de devices-tabel — de ID's worden alleen geteld en als referentie bewaard.
     *
     * @return array<int, array<string, mixed>>
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

            $naam = $get($row, 'Name');
            if ($naam === '') {
                continue;
            }

            $apparaatIds = [];
            $devicesRaw = $get($row, 'Devices');
            if ($devicesRaw !== '' && preg_match_all('/\d+/', $devicesRaw, $m)) {
                $apparaatIds = $m[0];
            }

            $rows[] = [
                'publisher' => $get($row, 'Publisher') ?: null,
                'naam' => $naam,
                'versie' => $get($row, 'Version') ?: null,
                'platform' => trim($get($row, 'Platform'), '[]') ?: null,
                'system_component' => strtolower($get($row, 'System Component')) === 'yes' ? 1 : 0,
                'eerst_gezien' => self::parseDatum($get($row, 'First')),
                'laatst_gezien' => self::parseDatum($get($row, 'Recent')),
                'aantal_apparaten' => count($apparaatIds),
                'apparaat_ids' => $apparaatIds !== [] ? implode(', ', $apparaatIds) : null,
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \RuntimeException('Geen software-rijen gevonden in dit CSV-bestand.');
        }

        return $rows;
    }

    private static function parseDatum(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($raw))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
