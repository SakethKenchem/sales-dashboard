<?php

namespace App\Filament\Resources\SalesManagers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesManagersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sales_records_count')
                    ->counts('salesRecords')
                    ->sortable()
                    ->label('Total Records'),
                TextColumn::make('sales_records_sum_total_achieved')
                    ->sum('salesRecords', 'total_achieved')
                    ->label('Achieved')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('sales_records_sum_qtr_tgt')
                    ->sum('salesRecords', 'qtr_tgt')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('weighted_q1_achvd')
                    ->label('%Achvd Q1')
                    ->state(function ($record): float {
                        $target = (float) $record->salesRecords()->sum('qtr_tgt');
                        $achieved = (float) $record->salesRecords()->sum('total_achieved');

                        if ($target <= 0) {
                            return 0;
                        }

                        return ($achieved / $target) * 100;
                    })
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('sales_records_sum_month_1')
                    ->sum('salesRecords', 'month_1')
                    ->label('JAN')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_records_sum_month_2')
                    ->sum('salesRecords', 'month_2')
                    ->label('FEB')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_records_sum_month_3')
                    ->sum('salesRecords', 'month_3')
                    ->label('MAR')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_records_sum_commit_month')
                    ->sum('salesRecords', 'commit_month')
                    ->label('Commit - MAR')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sales_records_sum_bal_to_achv')
                    ->sum('salesRecords', 'bal_to_achv')
                    ->label('Bal to Achv Q1')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendors')
                    ->relationship('salesRecords.vendor', 'name')
                    ->multiple(),
                SelectFilter::make('regions')
                    ->relationship('salesRecords.region', 'name')
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
