<?php

namespace App\Filament\Pages;

use App\Filament\Actions\DeduplicateSalesDataAction;
use App\Filament\Actions\ImportSalesExcelAction;
use App\Filament\Actions\WipeSalesDataAction;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            ImportSalesExcelAction::make(),
            DeduplicateSalesDataAction::make(),
            WipeSalesDataAction::make(),
        ];
    }
}
