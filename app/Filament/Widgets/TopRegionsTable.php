<?php

namespace App\Filament\Widgets;

use App\Models\Region;
use App\Support\SalesMetricsQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopRegionsTable extends BaseWidget
{
    protected static ?string $heading = 'Top Regions Performance';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Region::query()
                    ->leftJoinSub(SalesMetricsQuery::deduplicatedRows(), 'r', 'r.region_id', '=', 'regions.id')
                    ->select('regions.id', 'regions.name')
                    ->selectRaw('COUNT(r.region_id) as sales_records_count')
                    ->selectRaw('COALESCE(SUM(r.total_achieved), 0) as achieved_sum')
                    ->selectRaw('COALESCE(SUM(r.qtr_tgt), 0) as qtr_target_sum')
                    ->groupBy('regions.id', 'regions.name')
                    ->orderByDesc('achieved_sum')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Region')
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
                TextColumn::make('q1_achvd_pct')
                    ->label('%Achvd Q1')
                    ->state(function ($record): float {
                        $target = (float) ($record->qtr_target_sum ?? 0);
                        $achieved = (float) ($record->achieved_sum ?? 0);

                        if ($target <= 0) {
                            return 0;
                        }

                        return ($achieved / $target) * 100;
                    })
                    ->numeric(decimalPlaces: 2),
            ]);
    }
}
