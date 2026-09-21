<?php
/**
 * Export Service
 * CSV and XLSX export for leads
 */

class ExportService
{
    private const CSV_COLUMNS = [
        'Business Name', 'Category', 'Phone', 'International Phone',
        'Email', 'Address', 'Website', 'Google Maps URL',
        'Rating', 'Reviews', 'City', 'Area', 'Lead Status', 'Date Collected',
    ];

    /**
     * Export leads as CSV
     */
    public function exportCsv(array $leads, string $filename = 'leads_export.csv'): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        fputcsv($output, self::CSV_COLUMNS);

        // Data rows
        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['business_name'] ?? '',
                $lead['search_category'] ?? $lead['primary_category'] ?? '',
                $lead['national_phone'] ?? '',
                $lead['international_phone'] ?? '',
                $lead['email'] ?? '',
                $lead['formatted_address'] ?? '',
                $lead['website_url'] ?? '',
                $lead['google_maps_url'] ?? '',
                $lead['rating'] ?? '',
                $lead['review_count'] ?? '',
                $lead['search_city'] ?? '',
                $lead['search_area'] ?? '',
                $lead['lead_status'] ?? '',
                $lead['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export leads as XLSX (XML-based, no external dependencies)
     */
    public function exportXlsx(array $leads, string $filename = 'leads_export.xlsx'): void
    {
        // Create a minimal XLSX file using PHP ZipArchive and XML
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            Response::error('Failed to create export file');
            return;
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        // _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        // xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Leads" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>');

        // xl/styles.xml (minimal — header bold style)
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><name val="Calibri"/></font>
    </fonts>
    <fills count="2">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    </cellXfs>
</styleSheet>');

        // Build shared strings and sheet data
        $sharedStrings = [];
        $ssIndex = [];

        $addSharedString = function (string $str) use (&$sharedStrings, &$ssIndex): int {
            if (isset($ssIndex[$str])) return $ssIndex[$str];
            $idx = count($sharedStrings);
            $sharedStrings[] = $str;
            $ssIndex[$str] = $idx;
            return $idx;
        };

        // Prepare rows
        $rows = [];

        // Header row
        $headerRow = '';
        foreach (self::CSV_COLUMNS as $colIdx => $col) {
            $colLetter = chr(65 + $colIdx);
            $idx = $addSharedString($col);
            $headerRow .= "<c r=\"{$colLetter}1\" t=\"s\" s=\"1\"><v>{$idx}</v></c>";
        }
        $rows[] = "<row r=\"1\">{$headerRow}</row>";

        // Data rows
        $rowNum = 2;
        foreach ($leads as $lead) {
            $values = [
                $lead['business_name'] ?? '',
                $lead['search_category'] ?? $lead['primary_category'] ?? '',
                $lead['national_phone'] ?? '',
                $lead['international_phone'] ?? '',
                $lead['email'] ?? '',
                $lead['formatted_address'] ?? '',
                $lead['website_url'] ?? '',
                $lead['google_maps_url'] ?? '',
                $lead['rating'] ?? '',
                $lead['review_count'] ?? '',
                $lead['search_city'] ?? '',
                $lead['search_area'] ?? '',
                $lead['lead_status'] ?? '',
                $lead['created_at'] ?? '',
            ];

            $rowCells = '';
            foreach ($values as $colIdx => $val) {
                $colLetter = chr(65 + $colIdx);
                $val = (string) $val;
                $idx = $addSharedString($val);
                $rowCells .= "<c r=\"{$colLetter}{$rowNum}\" t=\"s\"><v>{$idx}</v></c>";
            }
            $rows[] = "<row r=\"{$rowNum}\">{$rowCells}</row>";
            $rowNum++;
        }

        // xl/worksheets/sheet1.xml
        $sheetData = implode("\n", $rows);
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>' . $sheetData . '</sheetData>
</worksheet>');

        // xl/sharedStrings.xml
        $ssCount = count($sharedStrings);
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $ssCount . '" uniqueCount="' . $ssCount . '">';
        foreach ($sharedStrings as $str) {
            $ssXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);

        $zip->close();

        // Send file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}
