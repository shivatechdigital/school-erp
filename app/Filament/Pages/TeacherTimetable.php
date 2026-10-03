<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherTimetableGrid;
use Filament\Pages\Page;

class TeacherTimetable extends Page
{
    use HasTeacherTimetableGrid;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'My Timetable';

    protected static ?string $title = 'My Timetable';

    protected string $view = 'filament.pages.teacher-timetable';

    public string $range = 'week';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getViewData(): array
    {
        return $this->timetableGridData();
    }
}
