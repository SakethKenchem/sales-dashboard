<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WipeSalesDataAction
{
    public static function make(): Action
    {
        return Action::make('wipeSalesData')
            ->label('Wipe Data')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Wipe imported sales data?')
            ->modalDescription('This removes sales records, regions, vendors, sales managers, and uploaded import files.')
            ->action(function (): void {
                $files = Storage::disk('local')->allFiles('imports');
                $fileCount = count($files);

                DB::transaction(function (): void {
                    DB::table('sales_records')->delete();
                    DB::table('regions')->delete();
                    DB::table('sales_managers')->delete();
                    DB::table('vendors')->delete();
                });

                if ($fileCount > 0) {
                    Storage::disk('local')->deleteDirectory('imports');
                }

                Notification::make()
                    ->title('Data wiped')
                    ->body("Database cleared and {$fileCount} uploaded file(s) removed.")
                    ->success()
                    ->send();
            });
    }
}
