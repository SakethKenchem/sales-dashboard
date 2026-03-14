<?php

namespace App\Filament\Resources\Regions\Pages;

use App\Filament\Actions\DeduplicateSalesDataAction;
use App\Filament\Actions\ImportSalesExcelAction;
use App\Filament\Actions\WipeSalesDataAction;
use App\Filament\Resources\Regions\RegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegions extends ListRecords
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportSalesExcelAction::make(),
            DeduplicateSalesDataAction::make(),
            WipeSalesDataAction::make(),
            CreateAction::make(),
        ];
    }
}
