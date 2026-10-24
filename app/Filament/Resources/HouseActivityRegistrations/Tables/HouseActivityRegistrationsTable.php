<?php

namespace App\Filament\Resources\HouseActivityRegistrations\Tables;

use App\Models\HouseActivityAssignment;
use App\Models\HouseActivityRegistration;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HouseActivityRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.name')->label('Activity')->searchable()->sortable(),
                TextColumn::make('house.name')->label('House'),
                TextColumn::make('student.full_name')->label('Student')->searchable()->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                })->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('appliedBy.name')->label('Applied By')->placeholder('—'),
                TextColumn::make('created_at')->label('Applied On')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'applied' => 'Applied',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
                SelectFilter::make('house_activity_id')->label('Activity')->relationship('activity', 'name'),
                SelectFilter::make('house_id')->label('House')->relationship('house', 'name'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (HouseActivityRegistration $record): bool => $record->status === 'applied'
                        && self::canDecide($record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('remarks')->label('Remarks (optional)')->rows(2),
                    ])
                    ->action(function (HouseActivityRegistration $record, array $data): void {
                        $cap = $record->activity->max_participants_per_house;

                        if ($cap !== null && $record->activity->approvedCountForHouse($record->house_id) >= $cap) {
                            Notification::make()->danger()->title("This house already has {$cap} approved participants")->send();

                            return;
                        }

                        $record->update([
                            'status' => 'approved',
                            'decided_by' => auth()->id(),
                            'decided_at' => now(),
                            'remarks' => $data['remarks'] ?? null,
                        ]);

                        self::notifyStudent($record, 'Your activity application was approved');
                        Notification::make()->success()->title('Registration approved')->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (HouseActivityRegistration $record): bool => $record->status === 'applied'
                        && self::canDecide($record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('remarks')->label('Reason (optional)')->rows(2),
                    ])
                    ->action(function (HouseActivityRegistration $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'decided_by' => auth()->id(),
                            'decided_at' => now(),
                            'remarks' => $data['remarks'] ?? null,
                        ]);

                        self::notifyStudent($record, 'Your activity application was rejected');
                        Notification::make()->success()->title('Registration rejected')->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function canDecide(HouseActivityRegistration $record): bool
    {
        if (in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true)) {
            return true;
        }

        return HouseActivityAssignment::query()
            ->where('house_activity_id', $record->house_activity_id)
            ->where('house_id', $record->house_id)
            ->where('incharge_user_id', auth()->id())
            ->exists();
    }

    private static function notifyStudent(HouseActivityRegistration $record, string $title): void
    {
        $user = $record->student?->user;

        if ($user) {
            Notification::make()->title($title)->body($record->activity->name)->sendToDatabase($user);
        }
    }
}
