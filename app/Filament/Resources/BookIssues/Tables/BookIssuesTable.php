<?php

namespace App\Filament\Resources\BookIssues\Tables;

use App\Models\BookIssue;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookIssuesTable
{
    public const FINE_PER_DAY = 5;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.title')
                    ->label('Book Title')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('member')
                    ->label('Borrower')
                    ->getStateUsing(fn (BookIssue $record): string => $record->member_type === 'student'
                        ? $record->student?->full_name.' (Student)'
                        : ($record->staff?->name ?? '-').' (Staff)'),

                TextColumn::make('issue_date')
                    ->label('Issued On')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (BookIssue $record): string => $record->status === 'issued' && $record->due_date->isPast() ? 'danger' : 'success'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'issued' => 'info',
                        'returned' => 'success',
                        'lost' => 'danger',
                        'damaged' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('fine_amount')
                    ->label('Fine')
                    ->money('INR')
                    ->color(fn (BookIssue $record): string => $record->fine_amount > 0 && $record->fine_status === 'unpaid' ? 'danger' : 'gray'),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'issued' => 'Currently Issued',
                        'returned' => 'Returned',
                        'lost' => 'Lost',
                        'damaged' => 'Damaged',
                    ]),
            ])
            ->recordActions([
                Action::make('markReturned')
                    ->label('Return Book')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->visible(fn (BookIssue $record): bool => $record->status === 'issued')
                    ->schema([
                        DatePicker::make('return_date')
                            ->label('Return Date')
                            ->default(now())
                            ->required(),

                        TextInput::make('fine_amount')
                            ->label('Late Fine / Penalty (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(function (BookIssue $record): int {
                                $daysLate = (int) $record->due_date->startOfDay()->diffInDays(today(), false);

                                return max(0, $daysLate) * self::FINE_PER_DAY;
                            }),

                        Select::make('fine_status')
                            ->options([
                                'paid' => 'Paid Now',
                                'unpaid' => 'Add to Pending Dues',
                                'waived' => 'Waived Off',
                            ])
                            ->default('paid'),
                    ])
                    ->action(function (BookIssue $record, array $data): void {
                        $record->update([
                            'status' => 'returned',
                            'return_date' => $data['return_date'],
                            'fine_amount' => $data['fine_amount'] ?? 0,
                            'fine_status' => $data['fine_status'] ?? 'paid',
                        ]);
                    }),

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
