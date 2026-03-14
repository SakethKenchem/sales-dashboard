<?php

namespace App\Filament\Widgets;

use App\Models\SalesRecord;
use Filament\Widgets\ChartWidget;

class MonthlyAchievementChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Achievement (Jan-Dec)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $totals = SalesRecord::query()
            ->selectRaw('COALESCE(SUM(month_1), 0) AS jan')
            ->selectRaw('COALESCE(SUM(month_2), 0) AS feb')
            ->selectRaw('COALESCE(SUM(month_3), 0) AS mar')
            ->selectRaw('COALESCE(SUM(month_4), 0) AS apr')
            ->selectRaw('COALESCE(SUM(month_5), 0) AS may')
            ->selectRaw('COALESCE(SUM(month_6), 0) AS jun')
            ->selectRaw('COALESCE(SUM(month_7), 0) AS jul')
            ->selectRaw('COALESCE(SUM(month_8), 0) AS aug')
            ->selectRaw('COALESCE(SUM(month_9), 0) AS sep')
            ->selectRaw('COALESCE(SUM(month_10), 0) AS oct')
            ->selectRaw('COALESCE(SUM(month_11), 0) AS nov')
            ->selectRaw('COALESCE(SUM(month_12), 0) AS dec')
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Achieved',
                    'data' => [
                        (float) ($totals?->jan ?? 0),
                        (float) ($totals?->feb ?? 0),
                        (float) ($totals?->mar ?? 0),
                        (float) ($totals?->apr ?? 0),
                        (float) ($totals?->may ?? 0),
                        (float) ($totals?->jun ?? 0),
                        (float) ($totals?->jul ?? 0),
                        (float) ($totals?->aug ?? 0),
                        (float) ($totals?->sep ?? 0),
                        (float) ($totals?->oct ?? 0),
                        (float) ($totals?->nov ?? 0),
                        (float) ($totals?->dec ?? 0),
                    ],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
