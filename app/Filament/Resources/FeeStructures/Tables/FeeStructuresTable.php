<?php

namespace App\Filament\Resources\FeeStructures\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeeStructuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academicYear.name')
                    ->label('Year')->badge(),
                TextColumn::make('class.name')
                    ->label('Class')->badge()->color('primary')->sortable(),
                TextColumn::make('feeHead.name')
                    ->label('Fee Head')->searchable()->weight('bold'),
                TextColumn::make('category')->badge(),
                TextColumn::make('amount')
                    ->money('INR')->sortable()->weight('bold'),
                TextColumn::make('fine_per_day')
                    ->label('Fine/Day')
                    ->money('INR')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('due_date')
                    ->date('d/m/Y'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->relationship('class', 'name')->label('Class'),
                SelectFilter::make('fee_head_id')
                    ->relationship('feeHead', 'name')->label('Fee Head'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
