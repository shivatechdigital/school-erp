<?php

namespace App\Filament\Resources\Exams\Tables;

use App\Models\Exam;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->badge()
                    ->color('info'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'final' => 'danger',
                        'mid_term' => 'warning',
                        'unit_test' => 'primary',
                        'practical' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('academicYear.name')
                    ->label('Year')
                    ->badge(),

                TextColumn::make('total_marks')
                    ->label('Max')
                    ->sortable(),

                TextColumn::make('pass_marks')
                    ->label('Pass')
                    ->color('danger'),

                TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date('d/m/Y'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'info',
                        'ongoing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('result_published')
                    ->label('Result')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-clock')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'unit_test' => 'Unit Test',
                        'mid_term' => 'Mid Term',
                        'final' => 'Final',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('publish_result')
                    ->label('📊 Publish Result')
                    ->icon('heroicon-o-megaphone')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Exam $record): bool => ! $record->result_published && $record->status === 'completed')
                    ->action(fn (Exam $record) => $record->update(['result_published' => true])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
