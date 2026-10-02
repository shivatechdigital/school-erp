<?php

namespace App\Filament\Resources\ExamSchedules;

use App\Filament\Resources\ExamSchedules\Pages\CreateExamSchedule;
use App\Filament\Resources\ExamSchedules\Pages\EditExamSchedule;
use App\Filament\Resources\ExamSchedules\Pages\ListExamSchedules;
use App\Filament\Resources\ExamSchedules\Schemas\ExamScheduleForm;
use App\Filament\Resources\ExamSchedules\Tables\ExamSchedulesTable;
use App\Models\ExamSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExamScheduleResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = ExamSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Examination';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Exam Schedule';

    public static function form(Schema $schema): Schema
    {
        return ExamScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamSchedulesTable::configure($table);
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
            'index' => ListExamSchedules::route('/'),
            'create' => CreateExamSchedule::route('/create'),
            'edit' => EditExamSchedule::route('/{record}/edit'),
        ];
    }
}
