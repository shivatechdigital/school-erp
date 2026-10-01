<?php

namespace App\Filament\Resources\Notices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NoticesTable
{
    public static function configure(Table $table): Table
    {
        $audienceLabels = [
            'all' => 'All School',
            'staff' => 'Staff Only',
            'students' => 'Students & Parents',
            'guardians' => 'Parents Only',
            'specific_class' => 'Specific Class',
        ];

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40)
                    ->weight('bold'),

                TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('target_audience')
                    ->label('Audience')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $audienceLabels[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('publish_date')
                    ->label('Publish Date')
                    ->date('d M Y')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('publisher.name')
                    ->label('Posted By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('publish_date', 'desc')
            ->filters([
                SelectFilter::make('priority')
                    ->options([
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                SelectFilter::make('target_audience')
                    ->label('Audience')
                    ->options($audienceLabels),
            ])
            ->recordActions([
                ViewAction::make(),
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
