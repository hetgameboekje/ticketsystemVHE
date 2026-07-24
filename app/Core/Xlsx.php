<?php

namespace App\Core;

class Xlsx
{
    private const EXCEL_EPOCH = '1899-12-30';

    public static function write(string $sheetName, array $headers, array $rows, array $dateColumns = [], string $author = 'Ticketsysteem Leen van Punt'): string
    {
        return self::writeMultiSheet([
            ['name' => $sheetName, 'headers' => $headers, 'rows' => $rows, 'dateColumns' => $dateColumns],
        ], $author);
    }

    /**
     * Zoals write(), maar met meerdere werkbladen in één workbook.
     * @param array<int, array{name: string, headers: array, rows: array, dateColumns?: array}> $sheets
     */
    public static function writeMultiSheet(array $sheets, string $author = 'Ticketsysteem Leen van Punt'): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');
        $sheetNames = array_column($sheets, 'name');

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', self::contentTypesXml(count($sheets)));
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('docProps/core.xml', self::coreXml($author, $now));
        $zip->addFromString('docProps/app.xml', self::appXml($sheetNames));
        $zip->addFromString('xl/workbook.xml', self::workbookXml($sheetNames));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml(count($sheets)));
        $zip->addFromString('xl/styles.xml', self::stylesXml());

        foreach ($sheets as $i => $sheet) {
            $headers = $sheet['headers'];
            $dateColumns = $sheet['dateColumns'] ?? [];
            $dateColumnIndexes = array_flip(array_map(
                fn (string $name) => array_search($name, $headers, true),
                array_intersect($dateColumns, $headers)
            ));
            $sheetXml = self::buildSheetXml($headers, $sheet['rows'], array_keys($dateColumnIndexes));
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $sheetXml);
        }

        $zip->close();

        $content = file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    public static function sheetNames(string $path): array
    {
        $zip = self::openZip($path);
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $zip->close();

        $names = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $names[] = (string) $sheet['name'];
        }

        return $names;
    }

    public static function readSheet(string $path, string $sheetName): array
    {
        $zip = self::openZip($path);

        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));

        $rIdToTarget = [];
        foreach ($rels->Relationship as $rel) {
            $rIdToTarget[(string) $rel['Id']] = (string) $rel['Target'];
        }

        $target = null;
        foreach ($workbook->sheets->sheet as $sheet) {
            if ((string) $sheet['name'] === $sheetName) {
                $rId = (string) $sheet->attributes('r', true)['id'];
                $target = $rIdToTarget[$rId] ?? null;
                break;
            }
        }

        if ($target === null) {
            $zip->close();
            return ['headers' => [], 'rows' => []];
        }

        $sharedStrings = [];
        $sstXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstXml !== false) {
            $sst = simplexml_load_string($sstXml);
            foreach ($sst->si as $si) {
                $sharedStrings[] = self::siText($si);
            }
        }

        $sheetXml = $zip->getFromName('xl/' . ltrim($target, '/'));
        $zip->close();

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $c) {
                $colIndex = self::colToIndex((string) $c['r']);
                $rowData[$colIndex] = self::cellValue($c, $sharedStrings);
            }
            if (!empty($rowData)) {
                $maxCol = max(array_keys($rowData));
                $line = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $line[] = $rowData[$i] ?? '';
                }
                $rows[] = $line;
            }
        }

        $headers = array_map('trim', array_shift($rows) ?? []);

        return ['headers' => $headers, 'rows' => $rows];
    }

    public static function excelSerialToDate($serial): ?string
    {
        if (!is_numeric($serial) || (float) $serial <= 0) {
            return null;
        }

        $date = new \DateTime(self::EXCEL_EPOCH);
        $date->modify('+' . (int) $serial . ' days');

        return $date->format('Y-m-d');
    }

    private static function openZip(string $path): \ZipArchive
    {
        $zip = new \ZipArchive();
        $result = $zip->open($path);

        if ($result !== true) {
            throw new \RuntimeException('Kon het Excel-bestand niet openen (geen geldig .xlsx-bestand).');
        }

        return $zip;
    }

    public static function dateToExcelSerial(string $date): int
    {
        $epoch = new \DateTime(self::EXCEL_EPOCH);
        $target = new \DateTime($date);

        return (int) $epoch->diff($target)->days;
    }

    private static function siText(\SimpleXMLElement $si): string
    {
        if (isset($si->t)) {
            return (string) $si->t;
        }

        $text = '';
        foreach ($si->r as $r) {
            $text .= (string) $r->t;
        }

        return $text;
    }

    private static function cellValue(\SimpleXMLElement $c, array $sharedStrings)
    {
        $type = (string) $c['t'];

        if ($type === 's') {
            $index = (int) $c->v;
            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr') {
            return isset($c->is) ? self::siText($c->is) : '';
        }

        if ($type === 'str' || $type === 'b') {
            return (string) $c->v;
        }

        return isset($c->v) ? (string) $c->v : '';
    }

    private static function colToIndex(string $ref): int
    {
        $col = preg_replace('/[0-9]/', '', $ref);
        $idx = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }

        return $idx - 1;
    }

    private static function indexToCol(int $index): string
    {
        $index++;
        $col = '';
        while ($index > 0) {
            $rem = ($index - 1) % 26;
            $col = chr(65 + $rem) . $col;
            $index = intdiv($index - 1, 26);
        }

        return $col;
    }

    private static function xmlEscape(string $value): string
    {
        // Regeltekens buiten tab/lf/cr zijn niet toegestaan in XML 1.0. Zulke tekens (bv. uit
        // geplakte Word-tekst) maken sheet1.xml ongeldig, waarna Excel het bestand als beschadigd
        // meldt en de betreffende celinformatie bij het openen verwijdert.
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function buildSheetXml(array $headers, array $rows, array $dateColumnIndexes): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';

        $xml .= '<row r="1">';
        foreach ($headers as $i => $header) {
            $ref = self::indexToCol($i) . '1';
            $xml .= '<c r="' . $ref . '" t="inlineStr" s="1"><is><t>' . self::xmlEscape((string) $header) . '</t></is></c>';
        }
        $xml .= '</row>';

        foreach ($rows as $rowIndex => $row) {
            $r = $rowIndex + 2;
            $xml .= '<row r="' . $r . '">';
            foreach ($row as $i => $value) {
                $ref = self::indexToCol($i) . $r;

                if ($value === null || $value === '') {
                    continue;
                }

                if (in_array($i, $dateColumnIndexes, true)) {
                    $serial = self::dateToExcelSerial((string) $value);
                    $xml .= '<c r="' . $ref . '" s="2"><v>' . $serial . '</v></c>';
                } elseif (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
                    $xml .= '<c r="' . $ref . '"><v>' . self::xmlEscape((string) $value) . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . self::xmlEscape((string) $value) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    private static function contentTypesXml(int $sheetCount): string
    {
        $overrides = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides
            . '</Types>';
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private static function coreXml(string $author, string $timestamp): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>' . self::xmlEscape($author) . '</dc:creator>'
            . '<cp:lastModifiedBy>' . self::xmlEscape($author) . '</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $timestamp . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    /** @param string[] $sheetNames */
    private static function appXml(array $sheetNames): string
    {
        $titleParts = implode('', array_map(
            fn (string $name) => '<vt:lpstr>' . self::xmlEscape($name) . '</vt:lpstr>',
            $sheetNames
        ));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Ticketsysteem Leen van Punt</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant">'
            . '<vt:variant><vt:lpstr>Werkbladen</vt:lpstr></vt:variant>'
            . '<vt:variant><vt:i4>' . count($sheetNames) . '</vt:i4></vt:variant>'
            . '</vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="' . count($sheetNames) . '" baseType="lpstr">'
            . $titleParts
            . '</vt:vector></TitlesOfParts>'
            . '<LinksUpToDate>false</LinksUpToDate>'
            . '<SharedDoc>false</SharedDoc>'
            . '<HyperlinksChanged>false</HyperlinksChanged>'
            . '</Properties>';
    }

    /** @param string[] $sheetNames */
    private static function workbookXml(array $sheetNames): string
    {
        $sheetTags = '';
        foreach ($sheetNames as $i => $name) {
            $n = $i + 1;
            $sheetTags .= '<sheet name="' . self::xmlEscape($name) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetTags . '</sheets>'
            . '</workbook>';
    }

    private static function workbookRelsXml(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="dd-mm-yyyy"/></numFmts>'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><sz val="11"/><name val="Calibri"/><b/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }
}
