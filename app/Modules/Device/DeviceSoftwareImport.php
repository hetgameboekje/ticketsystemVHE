<?php

namespace App\Modules\Device;

class DeviceSoftwareImport
{
    /**
     * Parseert een software-inventaris-CSV (bv. NinjaRMM-export met kolommen Publisher, Name,
     * Version, Platform, System Component, Recent, First, Devices). Slaat niets op — de controller
     * bepaalt eerst welk device_id erbij hoort (nieuw aanmaken of hergebruiken via
     * extern_apparaat_id) voordat de rijen weggeschreven worden via
     * DeviceSoftwareModel::replaceForDevice().
     *
     * @return array{extern_apparaat_id: ?string, rows: array<int, array<string, mixed>>}
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
        $externId = null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $naam = $get($row, 'Name');
            if ($naam === '') {
                continue;
            }

            if ($externId === null) {
                $devicesRaw = $get($row, 'Devices');
                if ($devicesRaw !== '' && preg_match('/\d+/', $devicesRaw, $m)) {
                    $externId = $m[0];
                }
            }

            $rows[] = [
                'publisher' => $get($row, 'Publisher') ?: null,
                'naam' => $naam,
                'versie' => $get($row, 'Version') ?: null,
                'platform' => trim($get($row, 'Platform'), '[]') ?: null,
                'system_component' => strtolower($get($row, 'System Component')) === 'yes' ? 1 : 0,
                'eerst_gezien' => self::parseDatum($get($row, 'First')),
                'laatst_gezien' => self::parseDatum($get($row, 'Recent')),
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \RuntimeException('Geen software-rijen gevonden in dit CSV-bestand.');
        }

        return ['extern_apparaat_id' => $externId, 'rows' => $rows];
    }

    /**
     * MySQL TIMESTAMP-kolommen accepteren alleen 1970-01-01 00:00:01 t/m 2038-01-19 03:14:07.
     * Exports bevatten soms datums met 2-cijferige jaartallen (bv. "1/14/62") die PHP als 2062
     * interpreteert, of andere onzinwaarden — die vallen buiten dat bereik en zouden de hele
     * import laten vastlopen op een SQL-fout. Zulke datums worden hier stilzwijgend leeg gelaten.
     */
    private static function parseDatum(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        try {
            $dt = new \DateTimeImmutable($raw);
        } catch (\Exception) {
            return null;
        }

        $min = new \DateTimeImmutable('1970-01-01 00:00:01');
        $max = new \DateTimeImmutable('2038-01-19 03:14:07');
        if ($dt < $min || $dt > $max) {
            return null;
        }

        return $dt->format('Y-m-d H:i:s');
    }
}
