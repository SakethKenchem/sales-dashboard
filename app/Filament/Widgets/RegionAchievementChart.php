<?php

namespace App\Filament\Widgets;

use App\Support\SalesMetricsQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RegionAchievementChart extends ChartWidget
{
    protected ?string $heading = 'Region Achievement vs Target';

    protected function getData(): array
    {
        $regions = DB::query()
            ->fromSub(SalesMetricsQuery::deduplicatedRows(), 'r')
            ->join('regions', 'regions.id', '=', 'r.region_id')
            ->selectRaw('regions.name as name')
            ->selectRaw('COALESCE(SUM(r.total_achieved), 0) as achieved_sum')
            ->selectRaw('COALESCE(SUM(r.qtr_tgt), 0) as target_sum')
            ->groupBy('regions.name')
            ->orderBy('regions.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Achieved',
                    'data' => $regions->pluck('achieved_sum')->map(fn($v) => (float) ($v ?? 0))->all(),
                ],
                [
                    'label' => 'QTR Target',
                    'data' => $regions->pluck('target_sum')->map(fn($v) => (float) ($v ?? 0))->all(),
                ],
            ],
            'labels' => $regions->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
