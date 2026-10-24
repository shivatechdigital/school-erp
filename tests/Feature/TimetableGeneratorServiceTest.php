<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\User;
use App\Services\TimetableGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private function baseConfig(): array
    {
        return [
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'periods_per_day' => 6,
            'period_duration' => 40,
            'day_start_time' => '08:00',
            'break_after' => [3],
            'break_duration' => 15,
            'replace_existing' => true,
        ];
    }

    private function makeFixtures(): array
    {
        $school = School::query()->create([
            'name' => 'Demo School', 'code' => 'SCH100', 'email' => 'school100@demo.com',
            'phone' => '9000000000', 'address' => 'Addr', 'city' => 'City', 'state' => 'State', 'pincode' => '110001',
        ]);

        $branch = Branch::query()->create(['school_id' => $school->id, 'name' => 'Main', 'code' => 'BR100']);

        $admin = User::factory()->create(['school_id' => $school->id, 'branch_id' => $branch->id, 'user_type' => 'school_admin']);
        $this->actingAs($admin);

        $year = AcademicYear::query()->create([
            'school_id' => $school->id, 'name' => '2026-2027', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'is_current' => true,
        ]);

        $class = SchoolClass::query()->create(['school_id' => $school->id, 'name' => 'Class 5', 'is_active' => true]);

        $sectionA = Section::query()->create([
            'school_id' => $school->id, 'branch_id' => $branch->id, 'class_id' => $class->id,
            'academic_year_id' => $year->id, 'name' => 'A', 'room_no' => 'R-A', 'is_active' => true,
        ]);
        $sectionB = Section::query()->create([
            'school_id' => $school->id, 'branch_id' => $branch->id, 'class_id' => $class->id,
            'academic_year_id' => $year->id, 'name' => 'B', 'room_no' => 'R-B', 'is_active' => true,
        ]);

        $math = Subject::query()->create(['school_id' => $school->id, 'name' => 'Math']);
        $science = Subject::query()->create(['school_id' => $school->id, 'name' => 'Science']);

        $mathTeacher = User::factory()->create(['school_id' => $school->id, 'branch_id' => $branch->id, 'user_type' => 'teacher']);
        $scienceTeacher = User::factory()->create(['school_id' => $school->id, 'branch_id' => $branch->id, 'user_type' => 'teacher']);

        // Same teacher teaches Math in BOTH sections — must not clash.
        $class->subjects()->attach($math->id, ['teacher_id' => $mathTeacher->id, 'periods_per_week' => 5]);
        $class->subjects()->attach($science->id, ['teacher_id' => $scienceTeacher->id, 'periods_per_week' => 5]);

        return compact('school', 'branch', 'admin', 'year', 'class', 'sectionA', 'sectionB', 'math', 'science', 'mathTeacher', 'scienceTeacher');
    }

    public function test_generates_clash_free_timetable_across_sections(): void
    {
        $fixtures = $this->makeFixtures();

        $result = (new TimetableGeneratorService)->generate($fixtures['class'], $fixtures['year'], $this->baseConfig());

        // 2 sections x 2 subjects x 5 periods/week = 20 teaching periods placed.
        $this->assertSame(20, $result['placed']);
        $this->assertSame([], $result['notes']);

        $teachingRows = Timetable::query()->where('is_break', false)->get();
        $this->assertCount(20, $teachingRows);

        // No teacher is double-booked at the same day+period across any section.
        $duplicate = $teachingRows->groupBy(fn (Timetable $t): string => "{$t->teacher_id}|{$t->day_of_week}|{$t->period_number}")
            ->filter(fn ($rows) => $rows->count() > 1);
        $this->assertCount(0, $duplicate);

        // Breaks: 5 working days x 1 break_after entry = 5 break rows per section = 10 total.
        $this->assertSame(10, Timetable::query()->where('is_break', true)->count());
    }

    public function test_replace_existing_clears_previous_rows_instead_of_duplicating(): void
    {
        $fixtures = $this->makeFixtures();
        $service = new TimetableGeneratorService;

        $service->generate($fixtures['class'], $fixtures['year'], $this->baseConfig());
        $firstCount = Timetable::query()->count();

        $service->generate($fixtures['class'], $fixtures['year'], $this->baseConfig());
        $secondCount = Timetable::query()->count();

        $this->assertSame($firstCount, $secondCount);
    }

    public function test_throws_when_class_has_no_subjects(): void
    {
        $fixtures = $this->makeFixtures();
        $emptyClass = SchoolClass::query()->create(['school_id' => $fixtures['school']->id, 'name' => 'Class 6', 'is_active' => true]);
        Section::query()->create([
            'school_id' => $fixtures['school']->id, 'branch_id' => $fixtures['branch']->id, 'class_id' => $emptyClass->id,
            'academic_year_id' => $fixtures['year']->id, 'name' => 'A', 'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);

        (new TimetableGeneratorService)->generate($emptyClass, $fixtures['year'], $this->baseConfig());
    }
}
