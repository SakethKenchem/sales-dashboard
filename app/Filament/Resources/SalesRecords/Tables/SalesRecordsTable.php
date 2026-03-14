<?php

namespace App\Filament\Resources\SalesRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('source_sheet')
                    ->label('Sheet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source_row')
                    ->label('Row')
                    ->sortable(),
                TextColumn::make('region.name')
                    ->label('Region')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salesManager.name')
                    ->label('CAM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month_1')->label('JAN')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_2')->label('FEB')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_3')->label('MAR')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_4')->label('APR')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_5')->label('MAY')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_6')->label('JUN')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_7')->label('JUL')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_8')->label('AUG')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_9')->label('SEP')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_10')->label('OCT')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_11')->label('NOV')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_12')->label('DEC')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_achieved')->label('Total Achieved')->numeric(decimalPlaces: 2),
                TextColumn::make('commit_month')->label('Commit (Import Month)')->numeric(decimalPlaces: 2),
                TextColumn::make('percent_achvd_q1')->label('%Achvd Q1')->numeric(decimalPlaces: 2),
                TextColumn::make('bal_to_achv')->label('Bal to Achv Q1')->numeric(decimalPlaces: 2),
                TextColumn::make('qtr_tgt')->label('QTR Target')->numeric(decimalPlaces: 2),
                TextColumn::make('yr_tgt')->label('YR Target')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Imported')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('region')
                    ->relationship('region', 'name')
                    ->multiple(),
                SelectFilter::make('sales_manager')
                    ->relationship('salesManager', 'name')
                    ->multiple(),
                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->multiple(),
                SelectFilter::make('source_sheet')
                    ->options(fn() => \App\Models\SalesRecord::query()
                        ->whereNotNull('source_sheet')
                        ->distinct()
                        ->orderBy('source_sheet')
                        ->pluck('source_sheet', 'source_sheet')
                        ->all()),
                Filter::make('month_range')
                    ->form([
                        Select::make('month_column')
                            ->label('Month')
                            ->options([
                                'month_1' => 'JAN',
                                'month_2' => 'FEB',
                                'month_3' => 'MAR',
                                'month_4' => 'APR',
                                'month_5' => 'MAY',
                                'month_6' => 'JUN',
                                'month_7' => 'JUL',
                                'month_8' => 'AUG',
                                'month_9' => 'SEP',
                                'month_10' => 'OCT',
                                'month_11' => 'NOV',
                                'month_12' => 'DEC',
                            ])
                            ->default('month_1')
                            ->required(),
                        TextInput::make('min')
                            ->numeric()
                            ->label('Min value'),
                        TextInput::make('max')
                            ->numeric()
                            ->label('Max value'),
                    ])
                    ->query(function ($query, array $data) {
                        $column = $data['month_column'] ?? 'month_1';

                        if (! in_array($column, [
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
                        ], true)) {
                            return $query;
                        }

                        return $query
                            ->when($data['min'] ?? null, fn($q, $min) => $q->where($column, '>=', (float) $min))
                            ->when($data['max'] ?? null, fn($q, $max) => $q->where($column, '<=', (float) $max));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
