<?php

namespace App\Filament\Resources\Timetables;

use App\Filament\Resources\Timetables\Pages\CreateTimetable;
use App\Filament\Resources\Timetables\Pages\EditTimetable;
use App\Filament\Resources\Timetables\Pages\ListTimetables;
use App\Filament\Resources\Timetables\Schemas\TimetableForm;
use App\Filament\Resources\Timetables\Tables\TimetablesTable;
use App\Models\Timetable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TimetableResource extends Resource
{
    protected static ?string $model = Timetable::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TimetableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimetablesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimetables::route('/'),
            'create' => CreateTimetable::route('/create'),
            'edit' => EditTimetable::route('/{record}/edit'),
        ];
    }
}
