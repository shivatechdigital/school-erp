<?php

namespace App\Filament\Resources\DepartmentRequests;

use App\Filament\Resources\DepartmentRequests\Pages\CreateDepartmentRequest;
use App\Filament\Resources\DepartmentRequests\Pages\ListDepartmentRequests;
use App\Filament\Resources\DepartmentRequests\Schemas\DepartmentRequestForm;
use App\Filament\Resources\DepartmentRequests\Tables\DepartmentRequestsTable;
use App\Models\Department;
use App\Models\DepartmentRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DepartmentRequestResource extends Resource
{
    protected static ?string $model = DepartmentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Department Requests';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return DepartmentRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        $query = parent::getEloquentQuery()->with(['department', 'raisedBy']);

        if (in_array($user?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true)) {
            return $query;
        }

        $headDepartmentIds = Department::query()
            ->where(fn (Builder $departments): Builder => $departments
                ->where('head_user_id', $user?->id)
                ->orWhere('sub_head_user_id', $user?->id))
            ->pluck('id');

        return $query->where(function (Builder $requests) use ($user, $headDepartmentIds): void {
            $requests->where('raised_by', $user?->id);

            if ($headDepartmentIds->isNotEmpty()) {
                $requests->orWhereIn('department_id', $headDepartmentIds);
            }
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartmentRequests::route('/'),
            'create' => CreateDepartmentRequest::route('/create'),
        ];
    }
}
