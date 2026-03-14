<?php

namespace App\Filament\Resources\SalesManagers\Pages;

use App\Filament\Resources\SalesManagers\SalesManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesManager extends EditRecord
{
    protected static string $resource = SalesManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
