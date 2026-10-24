<?php

namespace App\Filament\Resources\HouseActivities\Tables;

use App\Models\HouseActivity;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HouseActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('category')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'team_announced', 'completed' => 'success',
                    'registration_open' => 'warning',
                    'registration_closed' => 'info',
                    default => 'gray',
                })->formatStateUsing(fn (string $state): string => match ($state) {
                    'registration_open' => 'Registration Open',
                    'registration_closed' => 'Registration Closed',
                    'team_announced' => 'Team Announced',
                    'completed' => 'Completed',
                    default => 'Announced',
                }),
                TextColumn::make('max_participants_per_house')->label('Max per House')->placeholder('Unlimited'),
                TextColumn::make('registration_start_date')->date('d M Y')->label('Reg. Start')->placeholder('—'),
                TextColumn::make('registration_end_date')->date('d M Y')->label('Reg. End')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'announced' => 'Announced',
                    'registration_open' => 'Registration Open',
                    'registration_closed' => 'Registration Closed',
                    'team_announced' => 'Team Announced',
                    'completed' => 'Completed',
                ]),
                SelectFilter::make('category')->options([
                    'sports' => 'Sports',
                    'cultural' => 'Cultural',
                    'debate' => 'Debate / Quiz',
                    'other' => 'Other',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('openRegistration')
                    ->label('Open Registration')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->visible(fn (HouseActivity $record): bool => $record->status === 'announced')
                    ->requiresConfirmation()
                    ->action(fn (HouseActivity $record) => $record->update(['status' => 'registration_open'])),
                Action::make('closeRegistration')
                    ->label('Close Registration')
                    ->icon('heroicon-o-lock-closed')
                    ->color('info')
                    ->visible(fn (HouseActivity $record): bool => $record->status === 'registration_open')
                    ->requiresConfirmation()
                    ->action(fn (HouseActivity $record) => $record->update(['status' => 'registration_closed'])),
                Action::make('announceTeam')
                    ->label('Announce Team')
                    ->icon('heroicon-o-megaphone')
                    ->color('success')
                    ->visible(fn (HouseActivity $record): bool => $record->status === 'registration_closed')
                    ->requiresConfirmation()
                    ->action(function (HouseActivity $record): void {
                        $record->update(['status' => 'team_announced']);
                        Notification::make()->success()->title('Team announced')->send();
                    }),
                Action::make('markCompleted')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (HouseActivity $record): bool => $record->status === 'team_announced')
                    ->requiresConfirmation()
                    ->action(fn (HouseActivity $record) => $record->update(['status' => 'completed'])),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
