<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('class.name')
                    ->label('Class')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->badge(),

                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('student.roll_no')
                    ->label('Roll'),

                TextColumn::make('status')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'present' => 'heroicon-o-check-circle',
                        'absent' => 'heroicon-o-x-circle',
                        'late' => 'heroicon-o-clock',
                        'half_day' => 'heroicon-o-sun',
                        'leave' => 'heroicon-o-document-text',
                        default => 'heroicon-o-flag',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'half_day' => 'info',
                        'leave' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('markedBy.name')
                    ->label('Marked By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('class_id')
                    ->label('Class')
                    ->relationship('class', 'name'),

                SelectFilter::make('section_id')
                    ->label('Section')
                    ->relationship('section', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'half_day' => 'Half Day',
                        'leave' => 'Leave',
                    ]),

                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('to')
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('date', '>=', $d))
                            ->when($data['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('date', '<=', $d));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From: '.$data['from'];
                        }
                        if ($data['to'] ?? null) {
                            $indicators[] = 'To: '.$data['to'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_present')
                        ->label('✅ Mark Present')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'present'])),

                    BulkAction::make('mark_absent')
                        ->label('❌ Mark Absent')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'absent'])),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([25, 50, 100])
            ->emptyStateHeading('No attendance records')
            ->emptyStateDescription('Use "Mark Attendance" page to mark attendance.');
    }
}
