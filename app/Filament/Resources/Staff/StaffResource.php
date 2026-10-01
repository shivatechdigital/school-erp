<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Filament\Resources\Staff\Pages\ViewStaff;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StaffResource extends Resource
{
    public const STAFF_TYPES = [
        'school_admin', 'branch_admin', 'teacher',
        'accountant', 'librarian', 'transport_manager', 'receptionist',
    ];

    protected static ?string $model = User::class;

    protected static ?string $slug = 'staff';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Staff / Teachers';

    protected static ?string $modelLabel = 'Staff Member';

    // User has no tenant global scope, so school/branch filtering is applied here.
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->whereIn('user_type', self::STAFF_TYPES)
            ->when($user?->user_type !== 'super_admin', fn (Builder $query) => $query
                ->where('school_id', $user?->school_id)
                ->when($user?->branch_id, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId)));
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true);
    }

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'view' => ViewStaff::route('/{record}'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }
}
