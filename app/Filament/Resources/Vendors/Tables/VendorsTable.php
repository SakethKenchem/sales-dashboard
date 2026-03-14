<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Filament\Resources\Vendors\VendorResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorsTable
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
                    ->label('Total Records')
                    ->sortable(),
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
            ])
            ->filters([
                SelectFilter::make('regions')
                    ->relationship('salesRecords.region', 'name')
                    ->multiple(),
                SelectFilter::make('sales_managers')
                    ->relationship('salesRecords.salesManager', 'name')
                    ->multiple(),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make()
                    ->url(fn($record): string => VendorResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
