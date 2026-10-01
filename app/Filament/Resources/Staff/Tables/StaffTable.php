<?php

namespace App\Filament\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->circular(),

                TextColumn::make('employee_code')
                    ->label('Emp ID')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_type')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'school_admin' => 'danger',
                        'branch_admin' => 'warning',
                        'teacher' => 'primary',
                        'accountant' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('department')
                    ->searchable(),

                TextColumn::make('designation'),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('joining_date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('salary')
                    ->money('INR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label('Role')
                    ->options([
                        'teacher' => 'Teacher',
                        'school_admin' => 'School Admin',
                        'accountant' => 'Accountant',
                        'librarian' => 'Librarian',
                    ]),
                SelectFilter::make('department')
                    ->options([
                        'Mathematics' => 'Mathematics',
                        'Science' => 'Science',
                        'English' => 'English',
                        'Hindi' => 'Hindi',
                        'Admin' => 'Admin',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('joining_date', 'desc');
    }
}
