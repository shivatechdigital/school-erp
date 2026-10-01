<?php

namespace App\Filament\Resources\HostelAllocations\Tables;

use App\Models\HostelAllocation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HostelAllocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.admission_no')
                    ->label('Adm No.')
                    ->searchable(),

                TextColumn::make('student.first_name')
                    ->label('Student')
                    ->formatStateUsing(fn (HostelAllocation $record): string => (string) $record->student?->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold'),

                TextColumn::make('room.hostel.name')
                    ->label('Hostel')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('room.room_number')
                    ->label('Room No.'),

                TextColumn::make('bed_number')
                    ->label('Bed')
                    ->default('-'),

                TextColumn::make('allocated_date')
                    ->label('Allocated On')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'allocated' => 'success',
                        'vacated' => 'gray',
                        default => 'info',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'allocated' => 'Currently Staying',
                        'vacated' => 'Vacated',
                    ]),
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
