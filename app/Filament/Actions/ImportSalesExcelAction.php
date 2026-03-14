<?php

namespace App\Filament\Actions;

use App\Imports\SalesDataImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportSalesExcelAction
{
    public static function make(): Action
    {
        return Action::make('importSalesWorkbook')
            ->label('Import Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->form([
                FileUpload::make('workbook')
                    ->label('Sales workbook (.xlsx / .xls / .csv)')
                    ->disk('local')
                    ->directory('imports')
                    ->required()
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

                if ($replaceExisting) {
                    DB::transaction(function (): void {
                        DB::table('sales_records')->delete();
                        DB::table('regions')->delete();
                        DB::table('sales_managers')->delete();
                        DB::table('vendors')->delete();
                    });
                }

                $absolutePath = Storage::disk('local')->path($relativePath);
                $importer = app(SalesDataImport::class);
                $importer->autoAdjustWorkbookLayout($absolutePath);
                $summary = $importer->import($absolutePath);

                Notification::make()
                    ->title('Import completed')
                    ->body(sprintf(
                        'Sheets: %d | Imported rows: %d | Skipped rows: %d',
                        $summary['processed_sheets'],
                        $summary['imported_rows'],
                        $summary['skipped_rows'],
                    ))
                    ->success()
                    ->send();
            });
    }
}
