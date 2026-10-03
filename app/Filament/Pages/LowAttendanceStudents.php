<?php

namespace App\Filament\Pages;

use App\Models\Student;
use Filament\Pages\Page;

class LowAttendanceStudents extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Low Attendance (Students)';

    protected static ?string $title = 'Students Below 75% Attendance';

    protected string $view = 'filament.pages.low-attendance-students';

    public const THRESHOLD = 75.0;

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $rows = Student::query()
            ->with(['section.class'])
            ->where('status', 'active')
            ->withCount([
                'attendances as total_marked' => fn ($query) => $query->whereNull('period_number')->whereYear('date', now()->year),
                'attendances as present_count' => fn ($query) => $query->whereNull('period_number')->whereYear('date', now()->year)
                    ->whereIn('status', ['present', 'late', 'half_day']),
            ])
            ->get()
            ->map(fn (Student $student): array => [
                'student' => $student,
                'percentage' => $student->total_marked > 0 ? round($student->present_count / $student->total_marked * 100, 1) : null,
                'total_marked' => $student->total_marked,
            ])
            ->filter(fn (array $row): bool => $row['percentage'] !== null && $row['percentage'] < self::THRESHOLD)
            ->sortBy('percentage')
            ->values();

        return ['rows' => $rows];
    }
}
