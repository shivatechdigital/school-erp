<?php

namespace App\Filament\Resources\HouseActivityAssignments\Tables;

use App\Models\HouseActivityAssignment;
use App\Models\HouseActivityRegistration;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HouseActivityAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.name')->label('Activity')->searchable()->sortable(),
                TextColumn::make('house.name')->label('House'),
                TextColumn::make('incharge.name')->label('In-charge')->placeholder('Not assigned'),
                TextColumn::make('incharge_label')->label('Role')->placeholder('—'),
                TextColumn::make('captain.full_name')->label('Captain')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('house_activity_id')->label('Activity')->relationship('activity', 'name'),
                SelectFilter::make('house_id')->label('House')->relationship('house', 'name'),
            ])
            ->recordActions([
                Action::make('assignIncharge')
                    ->label('Assign In-charge')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true))
                    ->fillForm(fn (HouseActivityAssignment $record): array => [
                        'incharge_user_id' => $record->incharge_user_id,
                        'incharge_label' => $record->incharge_label,
                    ])
                    ->form([
                        Select::make('incharge_user_id')
                            ->label('In-charge')
                            ->options(fn (HouseActivityAssignment $record): array => User::query()
                                ->where('school_id', $record->activity->school_id)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        TextInput::make('incharge_label')
                            ->label('Role Label')
                            ->placeholder('e.g., Coach, Head, Mentor')
                            ->maxLength(255),
                    ])
                    ->action(fn (HouseActivityAssignment $record, array $data) => $record->update($data)),
                Action::make('setCaptain')
                    ->label('Set Captain')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (HouseActivityAssignment $record): bool => in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true)
                        || $record->isInchargeUser(auth()->id()))
                    ->fillForm(fn (HouseActivityAssignment $record): array => ['captain_student_id' => $record->captain_student_id])
                    ->form([
                        Select::make('captain_student_id')
                            ->label('Captain')
                            ->options(fn (HouseActivityAssignment $record): array => HouseActivityRegistration::query()
                                ->where('house_activity_id', $record->house_activity_id)
                                ->where('house_id', $record->house_id)
                                ->where('status', 'approved')
                                ->with('student')
                                ->get()
                                ->pluck('student.full_name', 'student_id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (HouseActivityAssignment $record, array $data): void {
                        $record->update($data);
                        Notification::make()->success()->title('Captain set')->send();
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->whereHas('activity', fn (Builder $activities) => $activities
                    ->when(auth()->user()?->user_type !== 'super_admin', fn (Builder $query) => $query->where('school_id', auth()->user()?->school_id))));
    }
}
