<?php

namespace App\Imports;

use App\Models\Region;
use App\Models\SalesRecord;
use App\Models\SalesManager;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SalesDataImport
{
    public function autoAdjustWorkbookLayout(string $absoluteFilePath): void
    {
        $spreadsheet = IOFactory::load($absoluteFilePath);

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $columnLetter = Coordinate::stringFromColumnIndex($column);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            $highestRow = $sheet->getHighestDataRow();
            for ($row = 1; $row <= $highestRow; $row++) {
                // -1 enables automatic height based on wrapped / rendered content.
                $sheet->getRowDimension($row)->setRowHeight(-1);
            }

            $sheet->calculateColumnWidths();
        }

        $this->createWriterForExtension($spreadsheet, $absoluteFilePath)->save($absoluteFilePath);
    }

    /**
     * Import all worksheets from a workbook regardless of sheet name or styling.
     *
     * @return array{processed_sheets:int, imported_rows:int, skipped_rows:int}
     */
    public function import(string $absoluteFilePath): array
    {
        $spreadsheet = IOFactory::load($absoluteFilePath);

        $summary = [
            'processed_sheets' => 0,
            'imported_rows' => 0,
            'skipped_rows' => 0,
        ];

        $regionCache = [];
        $managerCache = [];
        $vendorCache = [];

        DB::transaction(function () use ($spreadsheet, &$summary, &$regionCache, &$managerCache, &$vendorCache): void {
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $rows = $sheet->toArray(null, true, true, false);
                if (empty($rows)) {
                    continue;
                }

                $headerIndex = $this->detectHeaderRowIndex($rows);
                if ($headerIndex === null) {
                    continue;
                }

                $summary['processed_sheets']++;
                $headerMap = $this->buildHeaderMap($rows[$headerIndex]);

                foreach (array_slice($rows, $headerIndex + 1, null, true) as $rowIndex => $row) {
                    $regionName = $this->cleanString($this->valueByAliases($row, $headerMap, ['region']));
                    $managerName = $this->cleanString($this->valueByAliases($row, $headerMap, ['sales_manager']));
                    $vendorName = $this->cleanString($this->valueByAliases($row, $headerMap, ['vendor']));

                    if ($regionName === null || $managerName === null || $vendorName === null) {
                        $summary['skipped_rows']++;
                        continue;
                    }

                    $normalizedRegion = $this->normalizeHeader($regionName);
                    $normalizedManager = $this->normalizeHeader($managerName);
                    $normalizedVendor = $this->normalizeHeader($vendorName);

                    if (
                        in_array($normalizedRegion, ['total', 'subtotal', 'grandtotal'], true)
                        || in_array($normalizedManager, ['total', 'subtotal', 'grandtotal'], true)
                        || in_array($normalizedVendor, ['total', 'subtotal', 'grandtotal'], true)
                    ) {
                        $summary['skipped_rows']++;
                        continue;
                    }

                    $regionKey = Str::lower($regionName);
                    $managerKey = Str::lower($managerName);
                    $vendorKey = Str::lower($vendorName);

                    $region = $regionCache[$regionKey] ??= Region::firstOrCreate(['name' => $regionName]);
                    $manager = $managerCache[$managerKey] ??= SalesManager::firstOrCreate(['name' => $managerName]);
                    $vendor = $vendorCache[$vendorKey] ??= Vendor::firstOrCreate(['name' => $vendorName]);

                    SalesRecord::create([
                        'region_id' => $region->id,
                        'sales_manager_id' => $manager->id,
                        'vendor_id' => $vendor->id,
                        'yr_tgt' => $this->toFloat($this->valueByAliases($row, $headerMap, ['yr_tgt'])),
                        'qtr_tgt' => $this->toFloat($this->valueByAliases($row, $headerMap, ['qtr_tgt'])),
                        'mon_tgt' => $this->toFloat($this->valueByAliases($row, $headerMap, ['mon_tgt'])),
                        'month_1' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_1'])),
                        'month_2' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_2'])),
                        'month_3' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_3'])),
                        'month_4' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_4'])),
                        'month_5' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_5'])),
                        'month_6' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_6'])),
                        'month_7' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_7'])),
                        'month_8' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_8'])),
                        'month_9' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_9'])),
                        'month_10' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_10'])),
                        'month_11' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_11'])),
                        'month_12' => $this->toFloat($this->valueByAliases($row, $headerMap, ['month_12'])),
                        'total_achieved' => $this->toFloat($this->valueByAliases($row, $headerMap, ['total_achieved'])),
                        'commit_month' => $this->toFloat($this->valueByAliases($row, $headerMap, ['commit_month'])),
                        'percent_achvd_q1' => $this->toFloat($this->valueByAliases($row, $headerMap, ['percent_achvd_q1'])),
                        'bal_to_achv' => $this->toFloat($this->valueByAliases($row, $headerMap, ['bal_to_achv'])),
                        'source_sheet' => (string) $sheet->getTitle(),
                        'source_row' => (int) $rowIndex + 1,
                    ]);

                    $summary['imported_rows']++;
                }
            }
        });

        return $summary;
    }

    private function detectHeaderRowIndex(array $rows): ?int
    {
        foreach (array_slice($rows, 0, 25, true) as $index => $row) {
            $headerMap = $this->buildHeaderMap($row);
            if (isset($headerMap['region'], $headerMap['sales_manager'], $headerMap['vendor'])) {
                return $index;
            }
        }

        return null;
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $cell) {
            $normalized = $this->normalizeHeader((string) $cell);
            if ($normalized === '') {
                continue;
            }

            foreach ($this->aliases() as $canonical => $aliases) {
                if (in_array($normalized, $aliases, true) && ! isset($map[$canonical])) {
                    $map[$canonical] = $index;
                }
            }
        }

        return $map;
    }

    private function valueByAliases(array $row, array $headerMap, array $canonicalKeys): mixed
    {
        foreach ($canonicalKeys as $key) {
            if (! array_key_exists($key, $headerMap)) {
                continue;
            }

            $columnIndex = $headerMap[$key];
            $value = $row[$columnIndex] ?? null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function aliases(): array
    {
        return [
            'region' => ['region'],
            'sales_manager' => ['salesmanager', 'cam', 'manager', 'namesofsalesmanagers'],
            'vendor' => ['vendor', 'vendors', 'namesofvendors'],
            'yr_tgt' => ['yrtgt', 'yearlytarget', 'annualtarget'],
            'qtr_tgt' => ['qtrtgt', 'quartertarget', 'quarterlytgt'],
            'mon_tgt' => ['montgt', 'monthlytarget', 'monthtarget'],
            'month_1' => ['jan', 'january', 'month1', 'm1'],
            'month_2' => ['feb', 'february', 'month2', 'm2'],
            'month_3' => ['mar', 'march', 'month3', 'm3'],
            'month_4' => ['apr', 'month4', 'm4'],
            'month_5' => ['may', 'month5', 'm5'],
            'month_6' => ['jun', 'month6', 'm6'],
            'month_7' => ['jul', 'july', 'month7', 'm7'],
            'month_8' => ['aug', 'august', 'month8', 'm8'],
            'month_9' => ['sep', 'sept', 'september', 'month9', 'm9'],
            'month_10' => ['oct', 'october', 'month10', 'm10'],
            'month_11' => ['nov', 'november', 'month11', 'm11'],
            'month_12' => ['dec', 'december', 'month12', 'm12'],
            'total_achieved' => ['totalachieved', 'totalachvd', 'totalachv'],
            'commit_month' => ['commitmar', 'commitapr', 'commitmonth', 'commit'],
            'percent_achvd_q1' => ['achvdq1', 'percentachvdq1', 'achvdpercentq1', 'achvdq1percent'],
            'bal_to_achv' => ['baltoachvq1', 'baltoachv', 'balancetoachieveq1', 'bal'],
        ];
    }

    private function normalizeHeader(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        return $clean === '' ? null : $clean;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace([',', '%', ' '], '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function createWriterForExtension(Spreadsheet $spreadsheet, string $absoluteFilePath): BaseWriter
    {
        $extension = Str::lower(pathinfo($absoluteFilePath, PATHINFO_EXTENSION));

        $writerType = match ($extension) {
            'xls' => 'Xls',
            'csv' => 'Csv',
            default => 'Xlsx',
        };

        return IOFactory::createWriter($spreadsheet, $writerType);
    }
}
