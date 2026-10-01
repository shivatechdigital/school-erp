<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\BulkAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\StudentAttendance;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
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
