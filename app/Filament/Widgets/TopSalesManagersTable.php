<?php

namespace App\Filament\Widgets;

use App\Models\SalesManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSalesManagersTable extends BaseWidget
{
    protected static ?string $heading = 'Top CAM Performance';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SalesManager::query()
                    ->withCount('salesRecords')
                    ->withSum('salesRecords as achieved_sum', 'total_achieved')
                    ->withSum('salesRecords as qtr_target_sum', 'qtr_tgt')
                    ->orderByDesc('achieved_sum')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('CAM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sales_records_count')
                    ->label('Rows')
                    ->sortable(),
                TextColumn::make('achieved_sum')
                    ->label('Achieved')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('qtr_target_sum')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ]);
    }
}
