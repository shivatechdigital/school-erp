<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeeDefaulterResource\Pages;
use App\Models\FeeAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeeDefaulterResource extends Resource
{
    protected static ?string $model = FeeAssignment::class;

    protected static ?string $slug = 'fee-defaulters';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Fee Defaulters';

    protected static ?string $modelLabel = 'Fee Defaulter';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->user_type !== 'teacher';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['student.section.class', 'feeStructure.feeHead'])
            ->withSum(['collections as paid_amount' => fn (Builder $query) => $query->where('status', 'success')], 'total_amount')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->whereHas('student');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('student.admission_no')
                    ->label('Admission No'),

                TextColumn::make('student.section.class.name')
                    ->label('Class')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('student.section.name')
                    ->label('Section')
                    ->badge(),

                TextColumn::make('feeStructure.feeHead.name')
                    ->label('Fee Head'),

                TextColumn::make('net_amount')
                    ->label('Assigned')
                    ->money('INR'),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('INR')
                    ->state(fn (FeeAssignment $record): float => (float) ($record->paid_amount ?? 0)),

                TextColumn::make('balance_due')
                    ->label('Balance Due')
                    ->state(fn (FeeAssignment $record): float => (float) $record->net_amount - (float) ($record->paid_amount ?? 0))
                    ->money('INR')
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('due_date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('days_overdue')
                    ->label('Days Overdue')
                    ->state(fn (FeeAssignment $record): int => max(0, now()->diffInDays($record->due_date, false) * -1))
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'overdue' => 'danger',
                        'partial' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'overdue' => 'Overdue',
                    ]),
            ])
            ->defaultSort('due_date');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeDefaulters::route('/'),
        ];
    }
}
