<?php

namespace App\Filament\Resources\Marks\Tables;

use App\Models\Mark;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam.name')
                    ->label('Exam')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('student.roll_no')
                    ->label('Roll')
                    ->sortable(),

                TextColumn::make('class.name')
                    ->label('Class')
                    ->badge(),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(),

                TextColumn::make('theory_marks')
                    ->label('Theory')
                    ->sortable(),

                TextColumn::make('practical_marks')
                    ->label('Practical')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_marks')
                    ->label('Total')
                    ->weight('bold')
                    ->sortable()
                    ->color(fn (Mark $record): string => $record->total_marks >= $record->pass_marks ? 'success' : 'danger'),

                TextColumn::make('percentage')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('grade')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'A+', 'A' => 'success',
                        'B+', 'B' => 'primary',
                        'C' => 'warning',
                        'D' => 'gray',
                        'F' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('result')
                    ->badge()
                    ->icon(fn (?string $state): string => match ($state) {
                        'pass' => 'heroicon-o-check-circle',
                        'fail' => 'heroicon-o-x-circle',
                        'absent' => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pass' => 'success',
                        'fail' => 'danger',
                        'absent' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'N/A')),

                TextColumn::make('rank_in_class')
                    ->label('Rank')
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_id')
                    ->relationship('exam', 'name')
                    ->label('Exam'),
                SelectFilter::make('class_id')
                    ->relationship('class', 'name')
                    ->label('Class'),
                SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->label('Subject'),
                SelectFilter::make('result')
                    ->options([
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                        'absent' => 'Absent',
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
            ])
            ->defaultSort('id', 'desc');
    }
}
