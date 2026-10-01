<?php

namespace App\Filament\Resources\ExamSchedules\Tables;

use App\Models\ExamSchedule;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamSchedulesTable
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

                TextColumn::make('class.name')
                    ->label('Class')
                    ->badge(),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('exam_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn (ExamSchedule $record): string => $record->start_time.' - '.$record->end_time),

                TextColumn::make('max_marks')
                    ->label('Max'),

                TextColumn::make('room_no')
                    ->label('Room'),
            ])
            ->filters([
                SelectFilter::make('exam_id')
                    ->relationship('exam', 'name')
                    ->label('Exam'),
                SelectFilter::make('class_id')
                    ->relationship('class', 'name')
                    ->label('Class'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('exam_date');
    }
}
