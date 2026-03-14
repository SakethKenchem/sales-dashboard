<?php

namespace App\Filament\Actions;

use App\Support\SalesDataActionAuthorization;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeduplicateSalesDataAction
{
    public static function make(): Action
    {
        return Action::make('deduplicateSalesData')
            ->label('Deduplicate Data')
            ->icon('heroicon-o-sparkles')
            ->color('warning')
            ->visible(fn(): bool => Auth::check())
            ->requiresConfirmation()
            ->modalHeading('Remove duplicate sales rows?')
            ->modalDescription('This keeps the newest row and removes exact duplicate rows from sales_records.')
            ->action(function (): void {
                if (! SalesDataActionAuthorization::canManageData(Auth::user())) {
                    Notification::make()
                        ->title('Unauthorized action')
                        ->body('You are not allowed to deduplicate sales data.')
                        ->danger()
                        ->send();

                    return;
                }

                $availableColumns = Schema::getColumnListing('sales_records');

                $candidateColumns = [
                    'region_id',
                    'sales_manager_id',
                    'vendor_id',
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
                    'source_sheet',
                    'source_row',
                ];

                $groupColumns = array_values(array_intersect($candidateColumns, $availableColumns));

                if (count($groupColumns) < 3) {
                    Notification::make()
                        ->title('Deduplication skipped')
                        ->body('Not enough comparable columns found on sales_records.')
                        ->warning()
                        ->send();

                    return;
                }

                $deletedRows = 0;
                $affectedGroups = 0;

                DB::transaction(function () use (&$deletedRows, &$affectedGroups, $groupColumns): void {
                    $groups = DB::table('sales_records')
                        ->select(array_merge($groupColumns, [DB::raw('COUNT(*) as duplicate_count')]))
                        ->groupBy($groupColumns)
                        ->having('duplicate_count', '>', 1)
                        ->get();

                    foreach ($groups as $group) {
                        $query = DB::table('sales_records');

                        foreach ($groupColumns as $column) {
                            $value = $group->{$column};

                            if ($value === null) {
                                $query->whereNull($column);
                            } else {
                                $query->where($column, $value);
                            }
                        }

                        $ids = $query->orderByDesc('id')->pluck('id')->all();

                        if (count($ids) <= 1) {
                            continue;
                        }

                        // Keep the newest row; remove older exact duplicates.
                        array_shift($ids);

                        $deletedRows += DB::table('sales_records')
                            ->whereIn('id', $ids)
                            ->delete();

                        $affectedGroups++;
                    }
                });

                Notification::make()
                    ->title('Deduplication complete')
                    ->body("Removed {$deletedRows} row(s) across {$affectedGroups} duplicate group(s).")
                    ->success()
                    ->send();
            });
    }
}
