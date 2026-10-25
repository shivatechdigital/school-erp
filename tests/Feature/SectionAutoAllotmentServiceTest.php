<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\SectionAutoAllotmentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionAutoAllotmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    private function createStudent(User $schoolAdmin, Branch $branch, AcademicYear $academicYear, SchoolClass $class, string $firstName, string $admissionNo): Student
    {
        return Student::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $branch->id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'admission_no' => $admissionNo,
            'first_name' => $firstName,
            'last_name' => 'Test',
            'gender' => 'male',
            'date_of_birth' => '2012-01-01',
            'admission_date' => '2024-04-01',
            'status' => 'active',
        ]);
    }

    public function test_unassigned_students_fill_existing_sections_then_overflow_into_a_new_section(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        $branch = Branch::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Main',
            'code' => 'BR-'.uniqid(),
            'is_main_branch' => true,
        ]);
        $academicYear = AcademicYear::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => '2026-2027',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_current' => true,
        ]);
        $class = SchoolClass::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Class 6',
            'sort_order' => 6,
        ]);

        $sectionA = Section::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $branch->id,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'A',
            'capacity' => 40,
            'current_strength' => 0,
        ]);
        $sectionB = Section::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $branch->id,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'B',
            'capacity' => 40,
            'current_strength' => 0,
        ]);

        // One student already placed beforehand must be left untouched.
        $alreadyPlaced = $this->createStudent($schoolAdmin, $branch, $academicYear, $class, 'Aaron', 'ADM-PLACED');
        $alreadyPlaced->update(['section_id' => $sectionA->id]);

        for ($i = 1; $i <= 90; $i++) {
            $this->createStudent($schoolAdmin, $branch, $academicYear, $class, sprintf('Student%03d', $i), sprintf('ADM-%03d', $i));
        }

        $result = app(SectionAutoAllotmentService::class)->allot($class, $academicYear);

        $this->assertSame(90, $result['allotted']);
        $this->assertSame(39, $result['sections']['A']); // Section A had 1 slot already used.
        $this->assertSame(40, $result['sections']['B']);
        $this->assertSame(11, $result['sections']['C']);
        $this->assertSame(['C'], $result['created_sections']);

        $this->assertSame($alreadyPlaced->id, $alreadyPlaced->fresh()->id);
        $this->assertSame($sectionA->id, $alreadyPlaced->fresh()->section_id); // Untouched.

        $newSection = Section::query()->where('class_id', $class->id)->where('name', 'C')->firstOrFail();
        $this->assertSame(40, $newSection->capacity);
        $this->assertSame(11, $newSection->current_strength);

        $this->assertSame(40, Student::query()->where('section_id', $sectionA->id)->count());
        $this->assertSame(40, Student::query()->where('section_id', $sectionB->id)->count());
        $this->assertSame(11, Student::query()->where('section_id', $newSection->id)->count());
    }
}
