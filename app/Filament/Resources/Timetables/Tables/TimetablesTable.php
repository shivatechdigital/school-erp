<?php

namespace App\Filament\Resources\Timetables\Tables;

use App\Filament\Resources\Timetables\Schemas\TimetableForm;
use App\Models\Timetable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimetablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Day')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'monday' => 'info',
                        'tuesday' => 'primary',
                        'wednesday' => 'warning',
                        'thursday' => 'success',
                        'friday' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('period_number')
                    ->label('Period')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('start_time')
                    ->label('Time Slot')
                    ->formatStateUsing(fn (Timetable $record): string => date('h:i A', strtotime($record->start_time)).' - '.date('h:i A', strtotime($record->end_time))),

                TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->formatStateUsing(fn (Timetable $record): string => $record->schoolClass?->name.' ('.$record->section?->name.')'),

                TextColumn::make('subject.name')
                    ->label('Subject / Event')
                    ->getStateUsing(fn (Timetable $record): string => $record->is_break
                        ? '☕ '.($record->break_label ?? 'Break')
                        : ($record->subject?->name ?? '-'))
                    ->weight(fn (Timetable $record): FontWeight => $record->is_break ? FontWeight::Normal : FontWeight::Bold),

                TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->formatStateUsing(fn (Timetable $record): string => ($record->teacher?->name ?? '-').($record->substitute_for_id ? ' (Substitute)' : ''))
                    ->icon('heroicon-m-user'),

                TextColumn::make('room_no')
                    ->label('Room')
                    ->default('-')
                    ->badge(),
            ])
            ->defaultSort('period_number')
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Day')
                    ->options(TimetableForm::DAYS),

                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('schoolClass', 'name'),

                SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->relationship('teacher', 'name', fn (Builder $query) => $query->sameSchool()),
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
