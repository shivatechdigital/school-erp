<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use App\Models\LeaveType;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Leave Requests';

    protected static ?string $modelLabel = 'Leave Request';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->user_type !== 'teacher';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->with(['user', 'leaveType', 'approver'])
            ->when($user?->user_type !== 'super_admin' && $user?->branch_id, fn (Builder $query) => $query
                ->whereHas('user', fn (Builder $users) => $users->where('branch_id', $user->branch_id)));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge(),

                TextColumn::make('from_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('to_date')
                    ->date('d M Y'),

                TextColumn::make('total_days')
                    ->label('Days')
                    ->alignCenter(),

                TextColumn::make('remaining_balance')
                    ->label('Remaining Balance')
                    ->state(function (Leave $record): int {
                        $usedApproved = Leave::query()
                            ->where('user_id', $record->user_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('status', 'approved')
                            ->whereYear('from_date', now()->year)
                            ->sum('total_days');

                        $quota = (int) (LeaveType::query()->find($record->leave_type_id)?->max_days ?? 0);

                        return max(0, $quota - (int) $usedApproved);
                    })
                    ->suffix(' days')
                    ->alignCenter(),

                TextColumn::make('reason')
                    ->limit(40)
                    ->tooltip(fn (Leave $record): string => $record->reason)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('approver.name')
                    ->label('Decided By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Applied On')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship('leaveType', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Leave $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Leave $record): void {
                        $record->update(['status' => 'approved', 'approved_by' => auth()->id()]);
                        Notification::make()->success()->title('Leave approved')->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Leave $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Leave $record): void {
                        $record->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
                        Notification::make()->success()->title('Leave rejected')->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaves::route('/'),
        ];
    }
}
