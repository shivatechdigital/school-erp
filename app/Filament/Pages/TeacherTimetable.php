<?php

namespace App\Filament\Pages;

use App\Models\Timetable;
use Filament\Pages\Page;

class TeacherTimetable extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'My Timetable';

    protected static ?string $title = 'My Timetable';

    protected string $view = 'filament.pages.teacher-timetable';

    private const DAY_ORDER = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public string $range = 'week';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getViewData(): array
    {
        $today = strtolower(now()->format('l'));

        $allPeriods = Timetable::query()
            ->with(['schoolClass', 'section', 'subject', 'substituteFor'])
            ->where('teacher_id', auth()->id())
            ->orderBy('start_time')
            ->get();

        // Timetables don't carry calendar dates, only a recurring weekly pattern,
        // so "week" and "month" both render the same weekly template.
        $days = $this->range === 'today'
            ? [$today]
            : collect(self::DAY_ORDER)->filter(fn (string $day): bool => $allPeriods->contains('day_of_week', $day))->values()->all();

        $periods = $this->range === 'today' ? $allPeriods->where('day_of_week', $today) : $allPeriods;

        $timeSlots = $periods->map(fn (Timetable $period): string => "{$period->start_time}|{$period->end_time}")
            ->unique()->sort()->values();

        $grid = $timeSlots->map(function (string $slotKey) use ($periods, $days): array {
            [$start, $end] = explode('|', $slotKey);

            return [
                'start' => $start,
                'end' => $end,
                'days' => collect($days)->mapWithKeys(fn (string $day): array => [
                    $day => $periods->first(fn (Timetable $period): bool => $period->day_of_week === $day
                        && $period->start_time === $start && $period->end_time === $end),
                ])->all(),
            ];
        });

        return [
            'range' => $this->range,
            'today' => $today,
            'days' => $days,
            'grid' => $grid,
        ];
    }
}
