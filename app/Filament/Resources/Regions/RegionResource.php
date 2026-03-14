<?php

namespace App\Filament\Resources\Regions;

use App\Filament\Resources\Regions\Pages\CreateRegion;
use App\Filament\Resources\Regions\Pages\EditRegion;
use App\Filament\Resources\Regions\Pages\ListRegions;
use App\Filament\Resources\Regions\RelationManagers\SalesRecordsRelationManager;
use App\Filament\Resources\Regions\Schemas\RegionForm;
use App\Filament\Resources\Regions\Tables\RegionsTable;
use App\Models\Region;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;
    protected static ?string $navigationLabel = 'Regions';
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RegionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextEntry::make('name')
                    ->label('Region'),
                TextEntry::make('sales_records_count')
                    ->label('Rows Imported')
                    ->state(fn(Region $record): int => $record->salesRecords()->count()),
                TextEntry::make('qtr_target_total')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(Region $record): float => (float) $record->salesRecords()->sum('qtr_tgt')),
                TextEntry::make('achieved_total')
                    ->label('Total Achieved')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(Region $record): float => (float) $record->salesRecords()->sum('total_achieved')),
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
            'index' => ListRegions::route('/'),
            'create' => CreateRegion::route('/create'),
            'edit' => EditRegion::route('/{record}/edit'),
        ];
    }
}
