<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Collection;

class SectionAutoAllotmentService
{
    /**
     * Fills existing sections (in alphabetical order, up to each section's own capacity) with
     * currently unassigned students of the class, ordered alphabetically by name. Students left
     * over once every section is full spill into newly created sections (next letter onward).
     *
     * @return array{allotted: int, sections: array<string, int>, created_sections: array<string>}
     */
    public function allot(SchoolClass $class, AcademicYear $academicYear): array
    {
        $students = Student::query()
            ->where('class_id', $class->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->whereNull('section_id')
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get()
            ->values();

        if ($students->isEmpty()) {
            return ['allotted' => 0, 'sections' => [], 'created_sections' => []];
        }

        $sections = Section::query()
            ->where('class_id', $class->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $standardCapacity = (int) ($sections->max('capacity') ?: 40);
        $templateBranchId = $sections->first()?->branch_id;

        $summary = [];
        $createdSections = [];
        $cursor = 0;
        $total = $students->count();

        foreach ($sections as $section) {
            if ($cursor >= $total) {
                break;
            }

            $currentCount = Student::query()
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->count();

            $availableSlots = max(0, $section->capacity - $currentCount);

            if ($availableSlots <= 0) {
                continue;
            }

            $batch = $students->slice($cursor, $availableSlots);

            foreach ($batch as $student) {
                $student->update(['section_id' => $section->id]);
            }

            $placed = $batch->count();
            $cursor += $placed;

            if ($placed > 0) {
                $summary[$section->name] = $placed;
                $section->update(['current_strength' => $currentCount + $placed]);
            }
        }

        $nextLetter = $this->nextSectionLetter($sections->pluck('name'));

        while ($cursor < $total) {
            $newSection = Section::query()->create([
                'school_id' => $class->school_id,
                'branch_id' => $templateBranchId,
                'class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'name' => $nextLetter,
                'capacity' => $standardCapacity,
                'current_strength' => 0,
                'is_active' => true,
            ]);

            $batch = $students->slice($cursor, $standardCapacity);

            foreach ($batch as $student) {
                $student->update(['section_id' => $newSection->id]);
            }

            $placed = $batch->count();
            $cursor += $placed;
            $summary[$newSection->name] = $placed;
            $createdSections[] = $newSection->name;
            $newSection->update(['current_strength' => $placed]);

            $nextLetter = chr(ord($nextLetter) + 1);
        }

        return [
            'allotted' => $total,
            'sections' => $summary,
            'created_sections' => $createdSections,
        ];
    }

    /** @param  Collection<int, string>  $existingNames */
    private function nextSectionLetter(Collection $existingNames): string
    {
        $maxOrd = ord('A') - 1;

        foreach ($existingNames as $name) {
            if (preg_match('/^[A-Z]$/', (string) $name)) {
                $maxOrd = max($maxOrd, ord($name));
            }
        }

        return chr($maxOrd + 1);
    }
}
