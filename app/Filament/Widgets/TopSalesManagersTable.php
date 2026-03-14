<?php

namespace App\Filament\Widgets;

use App\Models\SalesManager;
use App\Support\SalesMetricsQuery;
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
                    ->leftJoinSub(SalesMetricsQuery::deduplicatedRows(), 'r', 'r.sales_manager_id', '=', 'sales_managers.id')
                    ->select('sales_managers.id', 'sales_managers.name')
                    ->selectRaw('COUNT(r.sales_manager_id) as sales_records_count')
                    ->selectRaw('COALESCE(SUM(r.total_achieved), 0) as achieved_sum')
                    ->selectRaw('COALESCE(SUM(r.qtr_tgt), 0) as qtr_target_sum')
                    ->groupBy('sales_managers.id', 'sales_managers.name')
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
