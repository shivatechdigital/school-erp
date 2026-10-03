<?php

namespace App\Filament\Pages\Concerns;

use App\Models\Timetable;

trait HasTeacherTimetableGrid
{
    private const TIMETABLE_WORKING_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    protected function timetableGridData(): array
    {
        $today = strtolower(now()->format('l'));
        $range = $this->range ?? 'week';

        $allPeriods = Timetable::query()
            ->with(['schoolClass', 'section', 'subject', 'substituteFor'])
            ->where('teacher_id', auth()->id())
            ->orderBy('start_time')
            ->get();

        // Timetables don't carry calendar dates, only a recurring weekly pattern,
        // so "week" and "month" both render the same full weekly grid (Mon-Sat columns).
        $days = $range === 'today' ? [$today] : self::TIMETABLE_WORKING_DAYS;

        $periods = $range === 'today' ? $allPeriods->where('day_of_week', $today) : $allPeriods;

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
            'range' => $range,
            'today' => $today,
            'days' => $days,
            'grid' => $grid,
        ];
    }
}
