<?php

namespace App\Filament\Widgets;

use App\Models\Region;
use App\Models\SalesManager;
use App\Support\SalesMetricsQuery;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesKpiOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totals = DB::query()
            ->fromSub(SalesMetricsQuery::deduplicatedRows(), 'r')
            ->selectRaw('COALESCE(SUM(qtr_tgt), 0) AS qtr_tgt_sum')
            ->selectRaw('COALESCE(SUM(total_achieved), 0) AS achieved_sum')
            ->selectRaw('COALESCE(SUM(yr_tgt), 0) AS yr_tgt_sum')
            ->selectRaw('COUNT(*) AS rows_count')
            ->first();

        $qtrTarget = (float) ($totals?->qtr_tgt_sum ?? 0);
        $achieved = (float) ($totals?->achieved_sum ?? 0);
        $yearTarget = (float) ($totals?->yr_tgt_sum ?? 0);
        $rows = (int) ($totals?->rows_count ?? 0);
        $achievementPct = $qtrTarget > 0 ? ($achieved / $qtrTarget) * 100 : 0;
        $yearRunRatePct = $yearTarget > 0 ? ($achieved / $yearTarget) * 100 : 0;

        return [
            Stat::make('Rows Imported', number_format($rows))
                ->description('All parsed rows from all workbook sheets'),
            Stat::make('Regions', (string) Region::query()->count())
                ->description('Active regions loaded from workbook'),
            Stat::make('Vendors', (string) Vendor::query()->count())
                ->description('Distinct vendors in all sheets'),
            Stat::make('CAM / Sales Managers', (string) SalesManager::query()->count())
                ->description('Distinct CAM records'),
            Stat::make('QTR Achievement', number_format($achievementPct, 2) . '%')
                ->description('Total Achieved vs QTR Target'),
            Stat::make('YR Run-Rate', number_format($yearRunRatePct, 2) . '%')
                ->description('Total Achieved vs Year Target'),
        ];
    }
}
