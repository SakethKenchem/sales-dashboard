<?php

namespace App\Filament\Resources\SalesManagers\Pages;

use App\Filament\Resources\SalesManagers\SalesManagerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesManager extends ViewRecord
{
    protected static string $resource = SalesManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
