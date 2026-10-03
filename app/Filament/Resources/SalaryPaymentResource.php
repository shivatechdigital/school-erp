<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryPaymentResource\Pages;
use App\Filament\Resources\Staff\StaffResource;
use App\Models\SalaryPayment;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SalaryPaymentResource extends Resource
{
    protected static ?string $model = SalaryPayment::class;

    protected static ?string $slug = 'salary-payments';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Salary Payments';

    protected static ?string $modelLabel = 'Salary Payment';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->user_type !== 'teacher';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'paidBy']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Staff')
                ->options(fn (): array => User::query()
                    ->whereIn('user_type', StaffResource::STAFF_TYPES)
                    ->where('school_id', auth()->user()->school_id)
                    ->when(auth()->user()->branch_id, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
                    ->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required(),

            DatePicker::make('period_month')
                ->label('Month')
                ->native(false)
                ->displayFormat('F Y')
                ->closeOnDateSelection()
                ->required(),

            TextInput::make('amount')
                ->numeric()
                ->prefix('₹')
                ->required(),

            Select::make('status')
                ->options(['pending' => 'Pending', 'paid' => 'Paid'])
                ->default('pending')
                ->required(),

            DatePicker::make('paid_on')
                ->native(false)
                ->displayFormat('d/m/Y'),

            Textarea::make('remark')
                ->maxLength(255)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('period_month')
                    ->label('Month')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'paid' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('paid_on')
                    ->date('d M Y')
                    ->placeholder('—'),

                TextColumn::make('paidBy.name')
                    ->label('Paid By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid']),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SalaryPayment $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (SalaryPayment $record): void {
                        $record->update(['status' => 'paid', 'paid_on' => now(), 'paid_by' => auth()->id()]);
                        Notification::make()->success()->title('Marked as paid')->send();
                    }),
                \Filament\Actions\EditAction::make(),
            ])
            ->defaultSort('period_month', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryPayments::route('/'),
            'create' => Pages\CreateSalaryPayment::route('/create'),
            'edit' => Pages\EditSalaryPayment::route('/{record}/edit'),
        ];
    }
}
