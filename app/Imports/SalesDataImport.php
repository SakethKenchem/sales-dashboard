<?php

namespace App\Imports;

use App\Models\Region;
use App\Models\SalesRecord;
use App\Models\SalesManager;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
    public function import(string $absoluteFilePath, ?string $sourceFileName = null): array
    {
        $spreadsheet = IOFactory::load($absoluteFilePath);

        $summary = [
            'processed_sheets' => 0,
            'imported_rows' => 0,
            'skipped_rows' => 0,
            'integrity_ok' => true,
            'integrity_message' => 'Integrity verified.',
        ];

        $regionCache = [];
        $managerCache = [];
        $vendorCache = [];
        $salesRecordColumns = array_flip(Schema::getColumnListing('sales_records'));
        $hasSourceTracking = isset($salesRecordColumns['source_sheet'], $salesRecordColumns['source_row']);
        $hasRowHash = isset($salesRecordColumns['row_hash']);
        $hasSourceFile = isset($salesRecordColumns['source_file']);

        $expectedTotals = [
            'qtr_tgt' => 0.0,
            'total_achieved' => 0.0,
        ];
        $writtenRowHashes = [];

        DB::transaction(function () use ($spreadsheet, $sourceFileName, &$summary, &$regionCache, &$managerCache, &$vendorCache, $salesRecordColumns, $hasSourceTracking, $hasRowHash, $hasSourceFile, &$expectedTotals, &$writtenRowHashes): void {
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
                $defaultIndexMap = $this->buildDefaultIndexMap($headerMap);

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

                    $payload = [
                        'region_id' => $region->id,
                        'sales_manager_id' => $manager->id,
                        'vendor_id' => $vendor->id,
                        'yr_tgt' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['yr_tgt'])),
                        'qtr_tgt' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['qtr_tgt'])),
                        'mon_tgt' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['mon_tgt'])),
                        'month_1' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_1'])),
                        'month_2' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_2'])),
                        'month_3' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_3'])),
                        'month_4' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_4'])),
                        'month_5' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_5'])),
                        'month_6' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_6'])),
                        'month_7' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_7'])),
                        'month_8' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_8'])),
                        'month_9' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_9'])),
                        'month_10' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_10'])),
                        'month_11' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_11'])),
                        'month_12' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['month_12'])),
                        'total_achieved' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['total_achieved'])),
                        'commit_month' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['commit_month'])),
                        'percent_achvd_q1' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['percent_achvd_q1'])),
                        'bal_to_achv' => $this->toFloat($this->valueByAliasesOrIndex($row, $headerMap, $defaultIndexMap, ['bal_to_achv'])),
                        'source_sheet' => (string) $sheet->getTitle(),
                        'source_row' => (int) $rowIndex + 1,
                        'source_file' => $sourceFileName,
                    ];

                    $payload = array_intersect_key($payload, $salesRecordColumns);

                    if ($hasRowHash) {
                        $payload['row_hash'] = $this->createRowHash($payload);
                        $writtenRowHashes[] = $payload['row_hash'];
                    }

                    $expectedTotals['qtr_tgt'] += (float) ($payload['qtr_tgt'] ?? 0);
                    $expectedTotals['total_achieved'] += (float) ($payload['total_achieved'] ?? 0);

                    $uniqueKeys = $hasRowHash
                        ? [
                            'row_hash' => $payload['row_hash'],
                        ]
                        : ($hasSourceTracking
                            ? [
                                'source_sheet' => (string) $sheet->getTitle(),
                                'source_row' => (int) $rowIndex + 1,
                            ]
                            : [
                                'region_id' => $region->id,
                                'sales_manager_id' => $manager->id,
                                'vendor_id' => $vendor->id,
                            ]);

                    SalesRecord::updateOrCreate($uniqueKeys, $payload);

                    $summary['imported_rows']++;
                }
            }

            if ($hasRowHash && count($writtenRowHashes) > 0) {
                $persisted = SalesRecord::query()
                    ->whereIn('row_hash', $writtenRowHashes)
                    ->selectRaw('COALESCE(SUM(qtr_tgt), 0) AS qtr_tgt_sum')
                    ->selectRaw('COALESCE(SUM(total_achieved), 0) AS total_achieved_sum')
                    ->first();

                $persistedQtr = round((float) ($persisted?->qtr_tgt_sum ?? 0), 2);
                $persistedAchieved = round((float) ($persisted?->total_achieved_sum ?? 0), 2);

                $expectedQtr = round($expectedTotals['qtr_tgt'], 2);
                $expectedAchieved = round($expectedTotals['total_achieved'], 2);

                if ($persistedQtr !== $expectedQtr || $persistedAchieved !== $expectedAchieved) {
                    $summary['integrity_ok'] = false;
                    $summary['integrity_message'] = sprintf(
                        'Mismatch detected. Expected QTR %.2f / Achieved %.2f, stored QTR %.2f / Achieved %.2f.',
                        $expectedQtr,
                        $expectedAchieved,
                        $persistedQtr,
                        $persistedAchieved,
                    );
                }
            } elseif (! $hasRowHash || ! $hasSourceFile) {
                $summary['integrity_ok'] = false;
                $summary['integrity_message'] = 'Strict integrity columns missing. Run migrations for row_hash/source_file.';
            }
        });

        return $summary;
    }

    private function createRowHash(array $payload): string
    {
        $hashPayload = $payload;

        unset($hashPayload['created_at'], $hashPayload['updated_at']);

        foreach ($hashPayload as $key => $value) {
            if (is_float($value) || is_int($value) || is_numeric($value)) {
                $hashPayload[$key] = number_format((float) $value, 6, '.', '');
            }
        }

        ksort($hashPayload);

        return hash('sha256', json_encode($hashPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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

            if (! isset($map['commit_month']) && $this->isCommitMonthHeader($normalized)) {
                $map['commit_month'] = $index;
            }
        }

        return $map;
    }

    private function isCommitMonthHeader(string $normalizedHeader): bool
    {
        if (! str_starts_with($normalizedHeader, 'commit')) {
            return false;
        }

        $suffix = substr($normalizedHeader, strlen('commit'));

        if ($suffix === '' || $suffix === 'month') {
            return true;
        }

        $validMonthTokens = [
            'jan',
            'january',
            'month1',
            'm1',
            'feb',
            'february',
            'month2',
            'm2',
            'mar',
            'march',
            'month3',
            'm3',
            'apr',
            'april',
            'month4',
            'm4',
            'may',
            'month5',
            'm5',
            'jun',
            'june',
            'month6',
            'm6',
            'jul',
            'july',
            'month7',
            'm7',
            'aug',
            'august',
            'month8',
            'm8',
            'sep',
            'sept',
            'september',
            'month9',
            'm9',
            'oct',
            'october',
            'month10',
            'm10',
            'nov',
            'november',
            'month11',
            'm11',
            'dec',
            'december',
            'month12',
            'm12',
        ];

        return in_array($suffix, $validMonthTokens, true);
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

    private function valueByAliasesOrIndex(array $row, array $headerMap, array $defaultIndexMap, array $canonicalKeys): mixed
    {
        $byHeader = $this->valueByAliases($row, $headerMap, $canonicalKeys);
        if ($byHeader !== null && $byHeader !== '') {
            return $byHeader;
        }

        foreach ($canonicalKeys as $key) {
            if (! array_key_exists($key, $defaultIndexMap)) {
                continue;
            }

            $index = $defaultIndexMap[$key];
            $value = $row[$index] ?? null;

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function buildDefaultIndexMap(array $headerMap): array
    {
        // Expected order after Vendor in most source templates:
        // YR TGT, QTR TGT, MON TGT, JAN...DEC, Total Achieved, Commit, %Achvd Q1, Bal to Achv Q1
        $vendorIndex = $headerMap['vendor'] ?? null;
        if (! is_int($vendorIndex)) {
            return [];
        }

        $keysInOrder = [
            'yr_tgt',
            'qtr_tgt',
            'mon_tgt',
            'month_1',
            'month_2',
            'month_3',
            'month_4',
            'month_5',
            'month_6',
            'month_7',
            'month_8',
            'month_9',
            'month_10',
            'month_11',
            'month_12',
            'total_achieved',
            'commit_month',
            'percent_achvd_q1',
            'bal_to_achv',
        ];

        $map = [];
        $offset = 1;

        foreach ($keysInOrder as $key) {
            if (isset($headerMap[$key])) {
                continue;
            }

            $map[$key] = $vendorIndex + $offset;
            $offset++;
        }

        return $map;
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
            'commit_month' => ['commitmonth', 'commit'],
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

        $stringValue = trim((string) $value);

        if ($stringValue === '-' || $stringValue === '--' || Str::lower($stringValue) === 'n/a') {
            return 0.0;
        }

        $isNegative = str_starts_with($stringValue, '(') && str_ends_with($stringValue, ')');
        $normalized = str_replace([',', '%', ' ', '$', '(', ')'], '', $stringValue);

        if (is_numeric($normalized)) {
            $float = (float) $normalized;

            return $isNegative ? -$float : $float;
        }

        return null;
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
