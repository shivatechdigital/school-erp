<?php

namespace App\Filament\Resources\FeeCollections\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeeCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_no')
                    ->label('Receipt')
                    ->badge()->color('info')
                    ->searchable()->sortable(),

                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('student.class.name')
                    ->label('Class')->badge(),

                TextColumn::make('amount')
                    ->money('INR')->sortable(),

                TextColumn::make('fine_amount')
                    ->label('Fine')
                    ->money('INR')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('payment_mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'upi' => 'primary',
                        'card' => 'info',
                        'online' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state))),

                TextColumn::make('payment_date')
                    ->date('d/m/Y')->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('payment_mode')
                    ->options([
                        'cash' => 'Cash', 'upi' => 'UPI',
                        'card' => 'Card', 'online' => 'Online',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->default(now()->startOfMonth())
                            ->native(false)->displayFormat('d/m/Y'),
                        DatePicker::make('to')
                            ->default(now())
                            ->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $q, array $d): Builder {
                        return $q
                            ->when($d['from'] ?? null, fn (Builder $q, $v) => $q->whereDate('payment_date', '>=', $v))
                            ->when($d['to'] ?? null, fn (Builder $q, $v) => $q->whereDate('payment_date', '<=', $v));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('payment_date', 'desc')
            ->paginated([25, 50, 100]);
    }
}
