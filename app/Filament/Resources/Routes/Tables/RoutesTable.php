<?php

namespace App\Filament\Resources\Routes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Route')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('vehicle.vehicle_number')
                    ->label('Vehicle')
                    ->badge()
                    ->color('primary')
                    ->default('Not Assigned'),

                TextColumn::make('start_point')
                    ->label('Start'),

                TextColumn::make('end_point')
                    ->label('Destination'),

                TextColumn::make('stops_count')
                    ->label('Total Stops')
                    ->counts('stops')
                    ->alignCenter(),

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
