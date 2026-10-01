<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Models\Vehicle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_number')
                    ->label('Reg. Number')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('vehicle_model')
                    ->label('Model')
                    ->default('-'),

                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('driver_name')
                    ->label('Driver')
                    ->description(fn (Vehicle $record): string => $record->driver_phone),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->recordActions([
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
