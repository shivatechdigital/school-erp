<?php

namespace App\Filament\Resources\Guardians\Tables;

use App\Models\Guardian;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GuardiansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('relation')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'father' => 'info',
                        'mother' => 'danger',
                        'guardian' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make('whatsapp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('occupation')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Children')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_primary_contact')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->trueColor('warning'),

                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('relation')
                    ->options([
                        'father' => 'Father',
                        'mother' => 'Mother',
                        'guardian' => 'Guardian',
                    ]),
                TernaryFilter::make('is_primary_contact')
                    ->label('Primary Contact'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('call')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->tooltip('Call Now')
                    ->url(fn (Guardian $record): string => 'tel:'.$record->phone),

                Action::make('whatsapp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->tooltip('WhatsApp')
                    ->url(fn (Guardian $record): string => 'https://wa.me/91'.ltrim($record->whatsapp ?? $record->phone, '0'))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No guardians found')
            ->emptyStateDescription('Add a guardian and link students to them.');
    }
}
