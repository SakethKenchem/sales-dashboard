<?php

namespace App\Filament\Resources\SalesManagers\Pages;

use App\Filament\Actions\ImportSalesExcelAction;
use App\Filament\Resources\SalesManagers\SalesManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesManagers extends ListRecords
{
    protected static string $resource = SalesManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportSalesExcelAction::make(),
            CreateAction::make(),
        ];
    }
}
