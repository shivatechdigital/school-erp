<?php

namespace App\Filament\Resources\Classrooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')->label('School')->searchable(),
                TextColumn::make('branch.name')->label('Branch')->placeholder('School-wide'),
                TextColumn::make('room_number')->label('Room Number / Name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('section.class.name')->label('Class')->placeholder('—'),
                TextColumn::make('section.name')->label('Section')->placeholder('—'),
                TextColumn::make('floor')->badge(),
                TextColumn::make('capacity')->numeric()->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
