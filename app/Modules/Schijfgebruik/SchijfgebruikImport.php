<?php

namespace App\Modules\Schijfgebruik;

class SchijfgebruikImport
{
    /**
     * Parseert een NinjaRMM "Devices"-export: één rij per apparaat, met een samengevoegde
     * "Volumes"-kolom die per schijf Name/Type/Capacity/Usage % bevat, bv.:
     * Name: "C:"/ Type: "Local Disk"/ Capacity: "314572795904 (293.0 GiB)"/ Usage %: "90%"
     *
     * @return array<int, array{device: array<string, mixed>, schijven: array<int, array<string, mixed>>}>
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

        $devices = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $naam = $get($row, 'Display Name');
            if ($naam === '') {
                continue;
            }

            $device = [
                'extern_id' => $get($row, 'Id') ?: null,
                'organisatie' => $get($row, 'Organization') ?: null,
                'locatie' => $get($row, 'Location') ?: null,
                'naam' => $naam,
                'type' => $get($row, 'Type') ?: null,
                'rol' => $get($row, 'Device Role') ?: null,
                'beleid' => $get($row, 'Policy') ?: null,
                'laatst_online' => self::parseDatum($get($row, 'Last Online')),
                'laatst_update' => self::parseDatum($get($row, 'Last Update')),
                'laatste_login' => $get($row, 'Last Login') ?: null,
                'ip_adressen' => $get($row, 'IP Addresses') ?: null,
                'mac_adressen' => $get($row, 'MAC Addresses') ?: null,
                'publiek_ip' => $get($row, 'Public IP') ?: null,
                'geheugen_gib' => self::parseFloat($get($row, 'Memory Capacity GiB')),
                'os_naam' => $get($row, 'OS Name') ?: null,
                'os_architectuur' => $get($row, 'OS Architecture') ?: null,
                'os_build' => $get($row, 'OS Build Number') ?: null,
                'merk' => $get($row, 'Device Make') ?: null,
                'model' => $get($row, 'Device Model') ?: null,
                'serienummer' => $get($row, 'Serial Number') ?: null,
                'domein' => $get($row, 'Domain') ?: null,
                'processor' => $get($row, 'Processors Name') ?: null,
                'tijdzone' => $get($row, 'Device Timezone') ?: null,
            ];

            $devices[] = [
                'device' => $device,
                'schijven' => self::parseVolumes($get($row, 'Volumes')),
            ];
        }

        fclose($handle);

        if (empty($devices)) {
            throw new \RuntimeException('Geen apparaat-rijen gevonden in dit CSV-bestand.');
        }

        return $devices;
    }

    /**
     * @return array<int, array{letter: string, disk_type: ?string, capaciteit_bytes: int, capaciteit_label: ?string, gebruik_percentage: int}>
     */
    private static function parseVolumes(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        preg_match_all(
            '/Name:\s*"([^"]*)"\s*\/\s*Type:\s*"([^"]*)"\s*\/\s*Capacity:\s*"(\d+)\s*\(([^)]*)\)"\s*\/\s*Usage\s*%:\s*"(\d+)%"/',
            $raw,
            $matches,
            PREG_SET_ORDER
        );

        $schijven = [];
        foreach ($matches as $m) {
            $schijven[] = [
                'letter' => $m[1],
                'disk_type' => $m[2] ?: null,
                'capaciteit_bytes' => (int) $m[3],
                'capaciteit_label' => $m[4] ?: null,
                'gebruik_percentage' => (int) $m[5],
            ];
        }

        return $schijven;
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

    private static function parseFloat(string $raw): ?float
    {
        return $raw === '' ? null : (float) str_replace(',', '.', $raw);
    }
}
