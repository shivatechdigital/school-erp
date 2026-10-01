<?php

namespace App\Filament\Resources\Hostels\Tables;

use App\Models\Hostel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HostelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Hostel Name')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', '-', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'boys' => 'info',
                        'girls' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('warden_name')
                    ->label('Warden')
                    ->description(fn (Hostel $record): string => $record->warden_phone ?? '-'),

                TextColumn::make('rooms_count')
                    ->label('Total Rooms')
                    ->counts('rooms')
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
