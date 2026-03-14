<?php

namespace App\Filament\Resources\SalesManagers;

use App\Filament\Resources\SalesManagers\Pages\CreateSalesManager;
use App\Filament\Resources\SalesManagers\Pages\EditSalesManager;
use App\Filament\Resources\SalesManagers\Pages\ListSalesManagers;
use App\Filament\Resources\SalesManagers\Pages\ViewSalesManager;
use App\Filament\Resources\SalesManagers\RelationManagers\SalesRecordsRelationManager;
use App\Filament\Resources\SalesManagers\Schemas\SalesManagerForm;
use App\Filament\Resources\SalesManagers\Tables\SalesManagersTable;
use App\Models\SalesManager;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesManagerResource extends Resource
{
    protected static ?string $model = SalesManager::class;
    protected static ?string $navigationLabel = 'Sales Managers';
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SalesManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesManagersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextEntry::make('name')
                    ->label('CAM / Sales Manager'),
                TextEntry::make('sales_records_count')
                    ->label('Rows Imported')
                    ->state(fn(SalesManager $record): int => $record->salesRecords()->count()),
                TextEntry::make('qtr_target_total')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(SalesManager $record): float => (float) $record->salesRecords()->sum('qtr_tgt')),
                TextEntry::make('achieved_total')
                    ->label('Total Achieved')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(SalesManager $record): float => (float) $record->salesRecords()->sum('total_achieved')),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SalesRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesManagers::route('/'),
            'create' => CreateSalesManager::route('/create'),
            'view' => ViewSalesManager::route('/{record}'),
            'edit' => EditSalesManager::route('/{record}/edit'),
        ];
    }
}
