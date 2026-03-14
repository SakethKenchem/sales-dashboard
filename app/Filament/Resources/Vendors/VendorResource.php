<?php

namespace App\Filament\Resources\Vendors;

use App\Filament\Resources\Vendors\Pages\CreateVendor;
use App\Filament\Resources\Vendors\Pages\EditVendor;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Filament\Resources\Vendors\RelationManagers\SalesRecordsRelationManager;
use App\Filament\Resources\Vendors\Schemas\VendorForm;
use App\Filament\Resources\Vendors\Tables\VendorsTable;
use App\Models\Vendor;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationLabel = 'Vendors';
    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return VendorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextEntry::make('name')
                    ->label('Vendor'),
                TextEntry::make('sales_records_count')
                    ->label('Rows Imported')
                    ->state(fn(Vendor $record): int => $record->salesRecords()->count()),
                TextEntry::make('qtr_target_total')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(Vendor $record): float => (float) $record->salesRecords()->sum('qtr_tgt')),
                TextEntry::make('achieved_total')
                    ->label('Total Achieved')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn(Vendor $record): float => (float) $record->salesRecords()->sum('total_achieved')),
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
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }
}
