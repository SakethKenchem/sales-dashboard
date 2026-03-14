<?php

namespace App\Filament\Widgets;

use App\Models\Region;
use Filament\Widgets\ChartWidget;

class RegionAchievementChart extends ChartWidget
{
    protected ?string $heading = 'Region Achievement vs Target';

    protected function getData(): array
    {
        $regions = Region::query()
            ->withSum('salesRecords as achieved_sum', 'total_achieved')
            ->withSum('salesRecords as target_sum', 'qtr_tgt')
            ->orderBy('name')
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
