<?php

namespace App\Filament\Resources\Homework\Tables;

use App\Models\Homework;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HomeworkTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(35)
                    ->weight('bold'),

                TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->formatStateUsing(fn (Homework $record): string => $record->schoolClass?->name.' ('.$record->section?->name.')'),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Homework $record): string => $record->due_date->isPast() ? 'danger' : 'success'),

                TextColumn::make('submissions_count')
                    ->label('Submitted')
                    ->counts('submissions')
                    ->alignCenter(),

                TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('schoolClass', 'name'),

                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name'),
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
