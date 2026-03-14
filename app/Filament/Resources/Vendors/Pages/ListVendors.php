<?php

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Actions\DeduplicateSalesDataAction;
use App\Filament\Actions\ImportSalesExcelAction;
use App\Filament\Actions\WipeSalesDataAction;
use App\Filament\Resources\Vendors\VendorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

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
