<?php

namespace App\Filament\Resources\DepartmentRequests\Tables;

use App\Models\DepartmentRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepartmentRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('bold'),
                TextColumn::make('type')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'issue' => 'Issue / Problem',
                    'item_request' => 'Item Request',
                    default => 'General Report',
                }),
                TextColumn::make('department.name')->label('Department'),
                TextColumn::make('raisedBy.name')->label('Raised By')->placeholder('—'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'resolved', 'resolved_by_head' => 'success',
                    'rejected' => 'danger',
                    'pending_admin' => 'warning',
                    default => 'gray',
                })->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending_head' => 'Pending Head',
                    'pending_admin' => 'Pending Admin',
                    'resolved_by_head' => 'Resolved by Head',
                    'resolved' => 'Resolved',
                    'rejected' => 'Rejected',
                    default => ucfirst($state),
                }),
                TextColumn::make('created_at')->label('Raised On')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending_head' => 'Pending Head',
                    'pending_admin' => 'Pending Admin',
                    'resolved_by_head' => 'Resolved by Head',
                    'resolved' => 'Resolved',
                    'rejected' => 'Rejected',
                ]),
                SelectFilter::make('type')->options([
                    'issue' => 'Issue / Problem',
                    'report' => 'General Report',
                    'item_request' => 'Item Request',
                ]),
                SelectFilter::make('department_id')->label('Department')->relationship('department', 'name'),
            ])
            ->recordActions([
                Action::make('resolveByHead')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (DepartmentRequest $record): bool => $record->status === 'pending_head'
                        && $record->department?->isHeadedBy(auth()->id()))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('head_remarks')->label('Remarks (optional)')->rows(2),
                    ])
                    ->action(function (DepartmentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'resolved_by_head',
                            'head_remarks' => $data['head_remarks'] ?? null,
                            'resolved_at' => now(),
                        ]);

                        if ($record->raisedBy) {
                            Notification::make()
                                ->title('Your request was resolved')
                                ->body($record->title)
                                ->success()
                                ->sendToDatabase($record->raisedBy);
                        }

                        Notification::make()->success()->title('Request resolved')->send();
                    }),
                Action::make('escalateToAdmin')
                    ->label('Escalate to Admin')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('warning')
                    ->visible(fn (DepartmentRequest $record): bool => $record->status === 'pending_head'
                        && $record->department?->isHeadedBy(auth()->id()))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('head_remarks')->label('Note for admin (optional)')->rows(2),
                    ])
                    ->action(function (DepartmentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'pending_admin',
                            'head_remarks' => $data['head_remarks'] ?? null,
                        ]);

                        foreach ($record->adminRecipients() as $admin) {
                            Notification::make()
                                ->title('Department request escalated')
                                ->body($record->title)
                                ->warning()
                                ->sendToDatabase($admin);
                        }

                        Notification::make()->success()->title('Escalated to admin')->send();
                    }),
                Action::make('resolveByAdmin')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (DepartmentRequest $record): bool => $record->status === 'pending_admin'
                        && in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('admin_remarks')->label('Remarks (optional)')->rows(2),
                    ])
                    ->action(function (DepartmentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'resolved',
                            'admin_remarks' => $data['admin_remarks'] ?? null,
                            'resolved_at' => now(),
                        ]);

                        if ($record->raisedBy) {
                            Notification::make()
                                ->title('Your request was resolved')
                                ->body($record->title)
                                ->success()
                                ->sendToDatabase($record->raisedBy);
                        }

                        Notification::make()->success()->title('Request resolved')->send();
                    }),
                Action::make('rejectByAdmin')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (DepartmentRequest $record): bool => $record->status === 'pending_admin'
                        && in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('admin_remarks')->label('Reason (optional)')->rows(2),
                    ])
                    ->action(function (DepartmentRequest $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'admin_remarks' => $data['admin_remarks'] ?? null,
                            'resolved_at' => now(),
                        ]);

                        if ($record->raisedBy) {
                            Notification::make()
                                ->title('Your request was rejected')
                                ->body($record->title)
                                ->danger()
                                ->sendToDatabase($record->raisedBy);
                        }

                        Notification::make()->success()->title('Request rejected')->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
