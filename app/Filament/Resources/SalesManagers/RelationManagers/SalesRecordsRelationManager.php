<?php

namespace App\Filament\Resources\SalesManagers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                // Data is normally imported from Excel. Keep edits focused in parent resources.
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('vendor.name')
            ->columns([
                TextColumn::make('region.name')
                    ->label('Region')
                    ->searchable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
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
                TextColumn::make('bal_to_achv')
                    ->label('Bal to Achv')
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
                SelectFilter::make('region')
                    ->relationship('region', 'name')
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make()->infolist([
                    TextEntry::make('region.name')->label('Region'),
                    TextEntry::make('vendor.name')->label('Vendor'),
                    TextEntry::make('yr_tgt')->label('YR Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('qtr_tgt')->label('QTR Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('mon_tgt')->label('MON Target')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_1')->label('Jan')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_2')->label('Feb')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_3')->label('Mar')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_4')->label('Apr')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_5')->label('May')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_6')->label('Jun')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_7')->label('Jul')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_8')->label('Aug')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_9')->label('Sep')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_10')->label('Oct')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_11')->label('Nov')->numeric(decimalPlaces: 2),
                    TextEntry::make('month_12')->label('Dec')->numeric(decimalPlaces: 2),
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
