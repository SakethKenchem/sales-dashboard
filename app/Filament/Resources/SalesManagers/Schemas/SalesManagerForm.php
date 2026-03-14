<?php

namespace App\Filament\Resources\SalesManagers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalesManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
