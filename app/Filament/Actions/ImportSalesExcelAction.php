<?php

namespace App\Filament\Actions;

use App\Imports\SalesDataImport;
use App\Support\SalesDataActionAuthorization;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImportSalesExcelAction
{
    public static function make(): Action
    {
        return Action::make('importSalesWorkbook')
            ->label('Import Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->visible(fn(): bool => SalesDataActionAuthorization::canManageData(Auth::user()))
            ->form([
                FileUpload::make('workbook')
                    ->label('Sales workbook (.xlsx / .xls / .csv)')
                    ->disk('local')
                    ->directory('imports')
                    ->required()
                    ->maxSize(10240)
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ]),
                Toggle::make('replace_existing')
                    ->label('Replace existing dashboard data before import')
                    ->default(true),
            ])
            ->action(function (array $data): void {
                if (! SalesDataActionAuthorization::canManageData(Auth::user())) {
                    Notification::make()
                        ->title('Unauthorized action')
                        ->body('You are not allowed to import sales workbooks.')
                        ->danger()
                        ->send();

                    return;
                }

                $relativePath = $data['workbook'] ?? null;
                $replaceExisting = (bool) ($data['replace_existing'] ?? false);

                if (! is_string($relativePath) || $relativePath === '') {
                    Notification::make()
                        ->title('Import failed')
                        ->body('No file was uploaded.')
                        ->danger()
                        ->send();

                    return;
                }

                $absolutePath = self::resolveValidatedWorkbookPath($relativePath);
                if ($absolutePath === null) {
                    Notification::make()
                        ->title('Import failed')
                        ->body('Invalid workbook path or file type. Please upload a valid Excel/CSV file again.')
                        ->danger()
                        ->send();

                    return;
                }

                if ($replaceExisting) {
                    DB::transaction(function (): void {
                        DB::table('sales_records')->delete();
                        DB::table('regions')->delete();
                        DB::table('sales_managers')->delete();
                        DB::table('vendors')->delete();
                    });
                }

                try {
                    $importer = app(SalesDataImport::class);
                    $importer->autoAdjustWorkbookLayout($absolutePath);
                    $summary = $importer->import($absolutePath, basename(str_replace('\\', '/', $relativePath)));
                } catch (Throwable $exception) {
                    Log::error('Workbook import failed.', [
                        'path' => $relativePath,
                        'error' => $exception->getMessage(),
                    ]);

                    Notification::make()
                        ->title('Import failed')
                        ->body('The workbook could not be imported. Please verify the file format and try again.')
                        ->danger()
                        ->send();

                    return;
                }

                $integrityLine = ($summary['integrity_ok'] ?? false)
                    ? 'Integrity: OK'
                    : ('Integrity: CHECK REQUIRED - ' . ($summary['integrity_message'] ?? 'Unknown mismatch'));

                Notification::make()
                    ->title('Import completed')
                    ->body(sprintf(
                        'Sheets: %d | Imported rows: %d | Skipped rows: %d | %s',
                        $summary['processed_sheets'],
                        $summary['imported_rows'],
                        $summary['skipped_rows'],
                        $integrityLine,
                    ))
                    ->color(($summary['integrity_ok'] ?? false) ? 'success' : 'warning')
                    ->send();
            });
    }

    private static function resolveValidatedWorkbookPath(string $relativePath): ?string
    {
        $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($normalized === '' || ! str_starts_with($normalized, 'imports/')) {
            return null;
        }

        if (str_contains($normalized, '../') || str_contains($normalized, '/..')) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($normalized)) {
            return null;
        }

        $absolutePath = $disk->path($normalized);
        $realPath = realpath($absolutePath);
        $importsRoot = realpath($disk->path('imports'));

        if ($realPath === false || $importsRoot === false || ! is_file($realPath)) {
            return null;
        }

        $normalizedRealPath = str_replace('\\', '/', $realPath);
        $normalizedImportsRoot = rtrim(str_replace('\\', '/', $importsRoot), '/');

        if (! str_starts_with($normalizedRealPath, $normalizedImportsRoot . '/')) {
            return null;
        }

        $extension = Str::lower((string) pathinfo($realPath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return null;
        }

        return $realPath;
    }
}
