<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Timetable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TimetableGeneratorService
{
    /**
     * Auto-assigns every subject's weekly periods across all sections of a class into a
     * clash-free grid (no teacher double-booked in the same day+period anywhere in the school).
     *
     * @param  array{working_days: array<int, string>, periods_per_day: int, period_duration: int, day_start_time: string, break_after: array<int, int>, break_duration: int, replace_existing: bool}  $config
     * @return array{placed: int, notes: array<int, string>}
     */
    public function generate(SchoolClass $schoolClass, AcademicYear $academicYear, array $config): array
    {
        $sections = $schoolClass->sections()->where('is_active', true)->get();

        if ($sections->isEmpty()) {
            throw new RuntimeException('This class has no active sections.');
        }

        $subjects = $schoolClass->subjects()->get();

        if ($subjects->isEmpty()) {
            throw new RuntimeException('No subjects are assigned to this class yet. Add them under the class\'s Subjects tab first.');
        }

        $daySchedule = $this->buildDaySchedule($config);
        $teachingSlots = array_values(array_filter($daySchedule, fn (array $slot): bool => ! $slot['is_break']));

        $placed = 0;
        $notes = [];

        DB::transaction(function () use ($sections, $subjects, $daySchedule, $teachingSlots, $academicYear, $config, &$placed, &$notes): void {
            if ($config['replace_existing']) {
                Timetable::query()
                    ->where('academic_year_id', $academicYear->id)
                    ->whereIn('section_id', $sections->pluck('id'))
                    ->delete();
            }

            // Seed clash-checks with whatever is already scheduled for other sections/classes.
            $teacherBookings = [];
            Timetable::query()
                ->where('academic_year_id', $academicYear->id)
                ->whereNotNull('teacher_id')
                ->get(['day_of_week', 'period_number', 'teacher_id'])
                ->each(function (Timetable $row) use (&$teacherBookings): void {
                    $teacherBookings["{$row->day_of_week}|{$row->period_number}"][$row->teacher_id] = true;
                });

            foreach ($sections as $section) {
                $this->insertBreaks($section, $academicYear, $daySchedule, $config['working_days'], $config['break_label'] ?? 'Break');

                $grid = [];
                foreach ($config['working_days'] as $day) {
                    foreach ($teachingSlots as $slot) {
                        $grid[$day][$slot['period_number']] = false;
                    }
                }

                foreach ($subjects->shuffle() as $subject) {
                    $teacherId = $subject->pivot->teacher_id;
                    $needed = max(0, (int) $subject->pivot->periods_per_week);

                    if ($needed === 0) {
                        continue;
                    }

                    if (! $teacherId) {
                        $notes[] = "{$section->name}: {$subject->name} has no teacher assigned — skipped.";

                        continue;
                    }

                    $placedForSubject = $this->placeSubject(
                        $section,
                        $academicYear,
                        $subject,
                        $teacherId,
                        $needed,
                        $config['working_days'],
                        $teachingSlots,
                        $grid,
                        $teacherBookings,
                    );

                    $placed += $placedForSubject;

                    if ($placedForSubject < $needed) {
                        $notes[] = "{$section->name}: {$subject->name} — only {$placedForSubject}/{$needed} periods placed (no free slot without a teacher clash).";
                    }
                }
            }
        });

        return ['placed' => $placed, 'notes' => $notes];
    }

    /**
     * @param  array<int, string>  $workingDays
     * @param  array<int, array<string, mixed>>  $teachingSlots
     * @param  array<string, array<int, bool>>  $grid
     * @param  array<string, array<int, bool>>  $teacherBookings
     */
    private function placeSubject(
        Section $section,
        AcademicYear $academicYear,
        Subject $subject,
        int $teacherId,
        int $needed,
        array $workingDays,
        array $teachingSlots,
        array &$grid,
        array &$teacherBookings,
    ): int {
        $placedForSubject = 0;
        $dayCountForSubject = array_fill_keys($workingDays, 0);

        // Pass 0 spreads the subject across different days first; pass 1 allows a second
        // period on the same day if the week doesn't have enough days to fit it otherwise.
        for ($pass = 0; $pass < 2 && $placedForSubject < $needed; $pass++) {
            foreach (collect($workingDays)->shuffle() as $day) {
                if ($placedForSubject >= $needed) {
                    break;
                }

                if ($pass === 0 && $dayCountForSubject[$day] >= 1) {
                    continue;
                }

                foreach (collect($teachingSlots)->shuffle() as $slot) {
                    $period = $slot['period_number'];

                    if ($grid[$day][$period]) {
                        continue;
                    }

                    $key = "{$day}|{$period}";

                    if (! empty($teacherBookings[$key][$teacherId])) {
                        continue;
                    }

                    $grid[$day][$period] = true;
                    $teacherBookings[$key][$teacherId] = true;
                    $dayCountForSubject[$day]++;

                    Timetable::query()->create([
                        'branch_id' => $section->branch_id,
                        'academic_year_id' => $academicYear->id,
                        'class_id' => $section->class_id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherId,
                        'day_of_week' => $day,
                        'period_number' => $period,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'room_no' => $section->room_no,
                        'is_break' => false,
                    ]);

                    $placedForSubject++;

                    break;
                }
            }
        }

        return $placedForSubject;
    }

    /** @param array<int, array<string, mixed>> $daySchedule */
    private function insertBreaks(Section $section, AcademicYear $academicYear, array $daySchedule, array $workingDays, string $breakLabel): void
    {
        foreach ($workingDays as $day) {
            foreach ($daySchedule as $slot) {
                if (! $slot['is_break']) {
                    continue;
                }

                Timetable::query()->create([
                    'branch_id' => $section->branch_id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $section->class_id,
                    'section_id' => $section->id,
                    'day_of_week' => $day,
                    'period_number' => $slot['period_number'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'is_break' => true,
                    'break_label' => $breakLabel,
                ]);
            }
        }
    }

    /**
     * Builds one day's sequence of teaching + break slots with computed start/end times.
     * The same structure is reused for every working day.
     *
     * @return array<int, array{period_number: int, is_break: bool, start_time: string, end_time: string}>
     */
    private function buildDaySchedule(array $config): array
    {
        $slots = [];
        $clock = Carbon::createFromFormat('H:i', $config['day_start_time']);
        $periodNumber = 1;

        for ($teachingIndex = 1; $teachingIndex <= $config['periods_per_day']; $teachingIndex++) {
            $start = $clock->copy();
            $end = $clock->copy()->addMinutes($config['period_duration']);

            $slots[] = [
                'period_number' => $periodNumber++,
                'is_break' => false,
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
            ];

            $clock = $end;

            if (in_array($teachingIndex, $config['break_after'], true)) {
                $breakEnd = $clock->copy()->addMinutes($config['break_duration']);

                $slots[] = [
                    'period_number' => $periodNumber++,
                    'is_break' => true,
                    'start_time' => $clock->format('H:i:s'),
                    'end_time' => $breakEnd->format('H:i:s'),
                ];

                $clock = $breakEnd;
            }
        }

        return $slots;
    }
}
