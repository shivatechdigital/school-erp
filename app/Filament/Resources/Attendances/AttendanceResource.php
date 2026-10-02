<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\BulkAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\AttendanceAccessGrant;
use App\Models\Section;
use App\Models\StudentAttendance;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = StudentAttendance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?string $modelLabel = 'Attendance Record';

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->when($user?->user_type === 'teacher', fn (Builder $query) => $query->whereHas('section', function (Builder $sections) use ($user): void {
                $sections->where('class_teacher_id', $user->id)
                    ->orWhereHas('attendanceAccessGrants', fn (Builder $grants) => $grants
                        ->where('teacher_id', $user->id)
                        ->whereNull('revoked_at')
                        ->where('valid_from', '<=', now())
                        ->where('valid_until', '>', now()));
            }));
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->user_type, ['super_admin', 'school_admin', 'branch_admin', 'teacher'], true)) {
            return false;
        }

        if ($user->user_type !== 'teacher') {
            return true;
        }

        return Section::query()->where('class_teacher_id', $user->id)->exists()
            || AttendanceAccessGrant::query()->where('teacher_id', $user->id)->whereNull('revoked_at')->where('valid_until', '>', now())->exists();
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
            'index' => ListAttendances::route('/'),
            'bulk' => BulkAttendance::route('/bulk'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Attendance Records')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort())
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn (): string => static::getUrl('index'))
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName().'.index', static::getRouteBaseName().'.edit')),

            NavigationItem::make('Mark Attendance')
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort())
                ->icon('heroicon-o-check-badge')
                ->url(fn (): string => static::getUrl('bulk'))
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName().'.bulk')),
        ];
    }
}
