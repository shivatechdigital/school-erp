<?php

namespace App\Filament\Resources\HostelRooms\Tables;

use App\Models\HostelRoom;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HostelRoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('hostel.name')
                    ->label('Hostel')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('room_number')
                    ->label('Room No.')
                    ->searchable(),

                TextColumn::make('room_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('bed_capacity')
                    ->label('Total Beds')
                    ->alignCenter(),

                TextColumn::make('active_allocations_count')
                    ->label('Occupied')
                    ->counts('activeAllocations')
                    ->alignCenter()
                    ->color(fn (HostelRoom $record): string => $record->active_allocations_count >= $record->bed_capacity ? 'danger' : 'success'),

                TextColumn::make('cost_per_bed')
                    ->label('Rent / Month')
                    ->money('INR'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('hostel_id')
                    ->label('Hostel')
                    ->relationship('hostel', 'name'),
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
