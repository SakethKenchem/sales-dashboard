<?php

namespace App\Filament\Resources\SalesRecords\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dimensions')
                    ->columns(3)
                    ->schema([
                        Select::make('region_id')
                            ->relationship('region', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('sales_manager_id')
                            ->label('CAM / Sales Manager')
                            ->relationship('salesManager', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                Section::make('Targets and Q1')
                    ->columns(4)
                    ->schema([
                        TextInput::make('yr_tgt')->numeric(),
                        TextInput::make('qtr_tgt')->numeric(),
                        TextInput::make('mon_tgt')->numeric(),
                        TextInput::make('total_achieved')->numeric(),
                        TextInput::make('commit_month')->label('Commit - MAR')->numeric(),
                        TextInput::make('percent_achvd_q1')->label('%Achvd Q1')->numeric(),
                        TextInput::make('bal_to_achv')->label('Bal to Achv Q1')->numeric(),
                    ]),
                Section::make('Monthly Achievement Jan-Dec')
                    ->columns(6)
                    ->schema([
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
                    ]),
                Section::make('Import Trace')
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_sheet')->maxLength(255),
                        TextInput::make('source_row')->numeric()->minValue(1),
                    ]),
            ]);
    }
}
