<?php

namespace App\Filament\Resources\StudentTransports\Tables;

use App\Models\StudentTransport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentTransportsTable
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
                    ->formatStateUsing(fn (StudentTransport $record): string => (string) $record->student?->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold'),

                TextColumn::make('route.title')
                    ->label('Route')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('stop.stop_name')
                    ->label('Pickup/Drop Stop')
                    ->description(fn (StudentTransport $record): string => $record->stop?->pickup_time
                        ? 'Pickup: '.date('h:i A', strtotime($record->stop->pickup_time))
                        : ''),

                TextColumn::make('stop.monthly_fare')
                    ->label('Monthly Fare')
                    ->money('INR'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('route_id')
                    ->label('Route')
                    ->relationship('route', 'title'),
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
