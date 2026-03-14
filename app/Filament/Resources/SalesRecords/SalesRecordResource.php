<?php

namespace App\Filament\Resources\SalesRecords;

use App\Filament\Resources\SalesRecords\Pages\CreateSalesRecord;
use App\Filament\Resources\SalesRecords\Pages\EditSalesRecord;
use App\Filament\Resources\SalesRecords\Pages\ListSalesRecords;
use App\Filament\Resources\SalesRecords\Schemas\SalesRecordForm;
use App\Filament\Resources\SalesRecords\Tables\SalesRecordsTable;
use App\Models\SalesRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesRecordResource extends Resource
{
    protected static ?string $model = SalesRecord::class;

    protected static ?string $navigationLabel = 'Sales Records';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return SalesRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesRecords::route('/'),
            'create' => CreateSalesRecord::route('/create'),
            'edit' => EditSalesRecord::route('/{record}/edit'),
        ];
    }
}
