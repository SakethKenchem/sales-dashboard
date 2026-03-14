<?php

namespace App\Filament\Widgets;

use App\Support\SalesMetricsQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyAchievementChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Achievement (Jan-Dec)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $monthMap = [
            'month_1' => 'Jan',
            'month_2' => 'Feb',
            'month_3' => 'Mar',
            'month_4' => 'Apr',
            'month_5' => 'May',
            'month_6' => 'Jun',
            'month_7' => 'Jul',
            'month_8' => 'Aug',
            'month_9' => 'Sep',
            'month_10' => 'Oct',
            'month_11' => 'Nov',
            'month_12' => 'Dec',
        ];

        $visibleMonths = [];

        foreach ($monthMap as $column => $label) {
            if (! SalesMetricsQuery::columnExists($column)) {
                continue;
            }

            if (in_array($column, ['month_1', 'month_2', 'month_3'], true) || SalesMetricsQuery::hasAnyData($column)) {
                $visibleMonths[$column] = $label;
            }
        }

        if ($visibleMonths === []) {
            return [
                'datasets' => [['label' => 'Achieved', 'data' => [0]]],
                'labels' => ['No month data'],
            ];
        }

        $query = DB::query()->fromSub(SalesMetricsQuery::deduplicatedRows(), 'r');

        foreach (array_keys($visibleMonths) as $column) {
            $query->selectRaw("COALESCE(SUM({$column}), 0) AS {$column}");
        }

        $totals = $query->first();

        $labels = array_values($visibleMonths);
        $data = [];

        foreach (array_keys($visibleMonths) as $column) {
            $data[] = (float) ($totals?->{$column} ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Achieved',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
