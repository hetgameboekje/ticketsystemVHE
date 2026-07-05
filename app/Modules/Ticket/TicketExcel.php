<?php

namespace App\Modules\Ticket;

use App\Core\Xlsx;
use App\Modules\Ticket\Models\TicketModel;
use App\Shared\Afdeling\Models\AfdelingModel;
use App\Shared\User\Models\UserModel;

class TicketExcel
{
    private const SHEET_NAME = 'Taken';

    private const HEADERS = [
        'Opdrachtgever', 'Taak', 'omschrijving', 'Afdeling', 'Prioriteit', 'Impact',
        'Schatting', 'behandeld', 'Datum toegevoegd', 'Schatting deadline', 'Behandelaar', 'Status',
    ];

    private const PRIORITEITEN = ['laag', 'normaal', 'hoog', 'kritiek'];
    private const STATUSSEN = ['open', 'in_behandeling', 'wacht_op_info', 'afgehandeld'];
    /** Oude statuswaarden (voor het samenvoegen van 'opgelost'/'gesloten' tot 'afgehandeld') — nog wel herkend bij import van oudere exports. */
    private const STATUS_ALIASSEN = ['opgelost' => 'afgehandeld', 'gesloten' => 'afgehandeld'];

    /** @param array $tickets Al gefilterde/gezochte tickets (zie TicketController::export()) — exporteert exact deze set. */
    public static function export(array $tickets, string $author = 'Ticketsysteem VHE'): string
    {
        $rows = [];
        foreach ($tickets as $t) {
            $rows[] = [
                $t['opdrachtgever_naam'],
                $t['titel'],
                $t['omschrijving'],
                $t['afdeling_naam'] ?? '',
                $t['prioriteit'],
                $t['impact'],
                $t['schatting_minuten'],
                $t['status'] === 'afgehandeld' ? 1 : 0,
                substr((string) $t['created_at'], 0, 10),
                $t['deadline'] ?? '',
                $t['behandelaar_naam'] ?? '',
                $t['status'],
            ];
        }

        return Xlsx::write(self::SHEET_NAME, self::HEADERS, $rows, ['Datum toegevoegd', 'Schatting deadline'], $author);
    }

    public static function import(string $filePath, int $importedDoorId): array
    {
        $sheetName = in_array(self::SHEET_NAME, Xlsx::sheetNames($filePath), true)
            ? self::SHEET_NAME
            : (Xlsx::sheetNames($filePath)[0] ?? null);

        if ($sheetName === null) {
            return ['created' => 0, 'skipped' => 0, 'errors' => ['Geen bruikbaar tabblad gevonden in het bestand.']];
        }

        $sheet = Xlsx::readSheet($filePath, $sheetName);
        $col = array_flip(array_map('strtolower', $sheet['headers']));

        $get = function (array $row, string $header) use ($col) {
            $i = $col[strtolower($header)] ?? null;
            return $i !== null ? trim((string) ($row[$i] ?? '')) : '';
        };

        $created = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = [];

        foreach ($sheet['rows'] as $rowNum => $row) {
            $titel = $get($row, 'Taak');
            if ($titel === '') {
                $skipped++;
                continue;
            }

            $opdrachtgever = $get($row, 'Opdrachtgever') ?: 'Onbekend';

            if (TicketModel::existsByTitelEnOpdrachtgever($titel, $opdrachtgever)) {
                $duplicates++;
                continue;
            }

            $omschrijving = $get($row, 'omschrijving');
            $notes = [];

            $prioriteitRaw = strtolower($get($row, 'Prioriteit'));
            if (in_array($prioriteitRaw, self::PRIORITEITEN, true)) {
                $prioriteit = $prioriteitRaw;
            } else {
                $prioriteit = 'normaal';
                if ($prioriteitRaw !== '') {
                    $notes[] = "Prioriteit (Excel): {$prioriteitRaw}";
                }
            }

            $afdelingNaam = $get($row, 'Afdeling');
            $afdelingId = $afdelingNaam !== '' ? AfdelingModel::findOrCreateByNaam($afdelingNaam) : null;

            $behandelaarNaam = $get($row, 'Behandelaar');
            $behandelaarId = $behandelaarNaam !== '' ? UserModel::findOrCreateByNaam($behandelaarNaam) : null;

            $behandeld = $get($row, 'behandeld') === '1';
            $statusNote = $get($row, 'Status');
            $statusRaw = strtolower(str_replace(' ', '_', $statusNote));

            $statusHerkend = in_array($statusRaw, self::STATUSSEN, true) || isset(self::STATUS_ALIASSEN[$statusRaw]);

            if (in_array($statusRaw, self::STATUSSEN, true)) {
                $status = $statusRaw;
            } elseif (isset(self::STATUS_ALIASSEN[$statusRaw])) {
                $status = self::STATUS_ALIASSEN[$statusRaw];
            } elseif ($behandeld) {
                $status = 'afgehandeld';
            } elseif ($statusNote !== '') {
                $status = 'in_behandeling';
            } else {
                $status = 'open';
            }

            if ($statusNote !== '' && !$statusHerkend) {
                $notes[] = "Status (Excel): {$statusNote}";
            }

            if (!empty($notes)) {
                $omschrijving = trim($omschrijving . "\n\n[" . implode('; ', $notes) . ']');
            }

            $deadlineRaw = $get($row, 'Schatting deadline');
            $deadline = $deadlineRaw !== '' ? Xlsx::excelSerialToDate($deadlineRaw) : null;

            $schattingRaw = $get($row, 'Schatting');
            $schatting = $schattingRaw !== '' && is_numeric($schattingRaw) ? (int) $schattingRaw : null;

            $id = TicketModel::create([
                'titel' => $titel,
                'omschrijving' => $omschrijving !== '' ? $omschrijving : '(geen omschrijving)',
                'opdrachtgever_naam' => $opdrachtgever,
                'afdeling_id' => $afdelingId,
                'prioriteit' => $prioriteit,
                'impact' => $get($row, 'Impact') ?: 'Normaal',
                'schatting_minuten' => $schatting,
                'deadline' => $deadline,
                'behandelaar_id' => $behandelaarId,
                'status' => $status,
                'aangemaakt_door_id' => $importedDoorId,
            ]);

            $datumToegevoegdRaw = $get($row, 'Datum toegevoegd');
            if ($datumToegevoegdRaw !== '') {
                $datum = Xlsx::excelSerialToDate($datumToegevoegdRaw);
                if ($datum !== null) {
                    TicketModel::setCreatedAt($id, $datum);
                }
            }

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'duplicates' => $duplicates, 'errors' => $errors];
    }
}
