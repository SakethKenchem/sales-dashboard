<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesMetricsQuery
{
    public static function deduplicatedRows(): Builder
    {
        return DB::table('sales_records')
            ->select('region_id', 'sales_manager_id', 'vendor_id')
            ->selectRaw(self::preferredExpr('yr_tgt') . ' as yr_tgt')
            ->selectRaw(self::preferredExpr('qtr_tgt') . ' as qtr_tgt')
            ->selectRaw(self::preferredExpr('total_achieved') . ' as total_achieved')
            ->selectRaw(self::preferredExpr('month_1') . ' as month_1')
            ->selectRaw(self::preferredExpr('month_2') . ' as month_2')
            ->selectRaw(self::preferredExpr('month_3') . ' as month_3')
            ->selectRaw(self::preferredExpr('month_4') . ' as month_4')
            ->selectRaw(self::preferredExpr('month_5') . ' as month_5')
            ->selectRaw(self::preferredExpr('month_6') . ' as month_6')
            ->selectRaw(self::preferredExpr('month_7') . ' as month_7')
            ->selectRaw(self::preferredExpr('month_8') . ' as month_8')
            ->selectRaw(self::preferredExpr('month_9') . ' as month_9')
            ->selectRaw(self::preferredExpr('month_10') . ' as month_10')
            ->selectRaw(self::preferredExpr('month_11') . ' as month_11')
            ->selectRaw(self::preferredExpr('month_12') . ' as month_12')
            ->groupBy('region_id', 'sales_manager_id', 'vendor_id');
    }

    public static function columnExists(string $column): bool
    {
        return Schema::hasColumn('sales_records', $column);
    }

    public static function hasAnyData(string $column): bool
    {
        if (! self::columnExists($column)) {
            return false;
        }

        return DB::table('sales_records')->whereNotNull($column)->exists();
    }

    private static function maxExpr(string $column): string
    {
        if (! self::columnExists($column)) {
            return '0';
        }

        return "MAX(COALESCE({$column}, 0))";
    }

    private static function preferredExpr(string $column): string
    {
        if (! self::columnExists($column)) {
            return '0';
        }

        if (! self::columnExists('source_sheet')) {
            return self::maxExpr($column);
        }

        // Prefer values imported from the Master sheet when available,
        // and fall back to the best available value from other sheets.
        return "COALESCE(MAX(CASE WHEN LOWER(COALESCE(source_sheet, '')) = 'master' AND {$column} IS NOT NULL THEN {$column} END), MAX(COALESCE({$column}, 0)))";
    }
}
