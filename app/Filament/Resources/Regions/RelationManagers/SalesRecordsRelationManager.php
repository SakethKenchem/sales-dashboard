<?php

namespace App\Filament\Resources\Regions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'salesRecords';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('sales_manager_id')
                    ->label('CAM / Sales Manager')
                    ->relationship('salesManager', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('yr_tgt')->numeric(),
                TextInput::make('qtr_tgt')->numeric(),
                TextInput::make('mon_tgt')->numeric(),
                TextInput::make('month_1')->label('Jan')->numeric(),
                TextInput::make('month_2')->label('Feb')->numeric(),
                TextInput::make('month_3')->label('Mar')->numeric(),
                TextInput::make('month_4')->label('Apr')->numeric(),
                TextInput::make('month_5')->label('May')->numeric(),
                TextInput::make('month_6')->label('Jun')->numeric(),
                TextInput::make('month_7')->label('Jul')->numeric(),
                TextInput::make('month_8')->label('Aug')->numeric(),
                TextInput::make('month_9')->label('Sep')->numeric(),
                TextInput::make('month_10')->label('Oct')->numeric(),
                TextInput::make('month_11')->label('Nov')->numeric(),
                TextInput::make('month_12')->label('Dec')->numeric(),
                TextInput::make('total_achieved')->numeric(),
                TextInput::make('commit_month')->label('Commit - MAR')->numeric(),
                TextInput::make('percent_achvd_q1')->label('%Achvd Q1')->numeric(),
                TextInput::make('bal_to_achv')->label('Bal to Achv Q1')->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('vendor.name')
            ->defaultGroup('salesManager.name')
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable(),
                TextColumn::make('salesManager.name')
                    ->label('CAM')
                    ->searchable(),
                TextColumn::make('qtr_tgt')
                    ->label('QTR Target')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('total_achieved')
                    ->label('Achieved')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('percent_achvd_q1')
                    ->label('% Achvd Q1')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('month_1')->label('Jan')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_2')->label('Feb')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_3')->label('Mar')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_4')->label('Apr')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_5')->label('May')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_6')->label('Jun')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_7')->label('Jul')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_8')->label('Aug')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_9')->label('Sep')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_10')->label('Oct')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_11')->label('Nov')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('month_12')->label('Dec')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor')
                    ->relationship('vendor', 'name')
                    ->multiple(),
                SelectFilter::make('sales_manager')
                    ->relationship('salesManager', 'name')
                    ->multiple(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()->infolist([
                    TextEntry::make('vendor.name')->label('Vendor'),
                    TextEntry::make('salesManager.name')->label('CAM'),
                    TextEntry::make('yr_tgt')->label('YR Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('qtr_tgt')->label('QTR Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('mon_tgt')->label('MON Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_1')->label('Jan')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_1)),
                    TextEntry::make('month_2')->label('Feb')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_2)),
                    TextEntry::make('month_3')->label('Mar')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_3)),
                    TextEntry::make('month_4')->label('Apr')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_4)),
                    TextEntry::make('month_5')->label('May')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_5)),
                    TextEntry::make('month_6')->label('Jun')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_6)),
                    TextEntry::make('month_7')->label('Jul')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_7)),
                    TextEntry::make('month_8')->label('Aug')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_8)),
                    TextEntry::make('month_9')->label('Sep')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_9)),
                    TextEntry::make('month_10')->label('Oct')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_10)),
                    TextEntry::make('month_11')->label('Nov')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_11)),
                    TextEntry::make('month_12')->label('Dec')->numeric(decimalPlaces: 2)->hidden(fn($record): bool => blank($record->month_12)),
                    TextEntry::make('total_achieved')->label('Total Achieved')->numeric(decimalPlaces: 2),
                    TextEntry::make('commit_month')->label('Commit - MAR')->numeric(decimalPlaces: 2),
                    TextEntry::make('percent_achvd_q1')->label('%Achvd Q1')->numeric(decimalPlaces: 2),
                    TextEntry::make('bal_to_achv')->label('Bal to Achv Q1')->numeric(decimalPlaces: 2),
                    TextEntry::make('source_sheet')->label('Source Sheet'),
                    TextEntry::make('source_row')->label('Source Row'),
                ]),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
