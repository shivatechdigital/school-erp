<?php

namespace Tests\Feature;

use App\Filament\Resources\HouseActivities\Pages\CreateHouseActivity;
use App\Filament\Resources\HouseActivityRegistrations\Pages\CreateHouseActivityRegistration;
use App\Filament\Resources\HouseActivityRegistrations\Pages\ListHouseActivityRegistrations;
use App\Models\AcademicYear;
use App\Models\House;
use App\Models\HouseActivity;
use App\Models\HouseActivityAssignment;
use App\Models\HouseActivityRegistration;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HouseActivityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    private function createStudent(User $schoolAdmin, string $admissionNo, int $branchId): Student
    {
        $academicYear = AcademicYear::query()->where('school_id', $schoolAdmin->school_id)->first()
            ?? AcademicYear::query()->create([
                'school_id' => $schoolAdmin->school_id,
                'name' => '2025-2026',
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_current' => true,
            ]);
        $class = SchoolClass::query()->where('school_id', $schoolAdmin->school_id)->first()
            ?? SchoolClass::query()->create([
                'school_id' => $schoolAdmin->school_id,
                'name' => 'Class 1',
                'sort_order' => 1,
            ]);

        return Student::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $branchId,
            'academic_year_id' => $academicYear->id,
            'class_id' => $class->id,
            'admission_no' => $admissionNo,
            'first_name' => 'Student',
            'last_name' => $admissionNo,
            'gender' => 'male',
            'date_of_birth' => '2012-01-01',
            'admission_date' => '2024-04-01',
            'status' => 'active',
        ]);
    }

    public function test_creating_an_activity_auto_assigns_every_active_house(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        House::query()->create(['school_id' => $schoolAdmin->school_id, 'name' => 'Red House']);
        House::query()->create(['school_id' => $schoolAdmin->school_id, 'name' => 'Blue House']);

        Livewire::test(CreateHouseActivity::class)
            ->fillForm([
                'name' => 'Inter-House Cricket',
                'category' => 'sports',
                'max_participants_per_house' => 2,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $activity = HouseActivity::query()->where('name', 'Inter-House Cricket')->firstOrFail();

        $this->assertSame(
            2,
            HouseActivityAssignment::query()->where('house_activity_id', $activity->id)->count(),
        );
    }

    public function test_a_student_can_apply_once_registration_is_open_and_the_house_is_derived(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        $house = House::query()->create(['school_id' => $schoolAdmin->school_id, 'name' => 'Green House']);
        $teacher = User::query()->where('user_type', 'teacher')->firstOrFail();
        $student = $this->createStudent($schoolAdmin, 'ADM-0001', $teacher->branch_id);
        $student->update(['house_id' => $house->id]);

        $activity = HouseActivity::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Inter-House Debate',
            'category' => 'debate',
            'status' => 'registration_open',
        ]);

        Livewire::test(CreateHouseActivityRegistration::class)
            ->fillForm([
                'house_activity_id' => $activity->id,
                'student_id' => $student->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('house_activity_registrations', [
            'house_activity_id' => $activity->id,
            'student_id' => $student->id,
            'house_id' => $house->id,
            'status' => 'applied',
        ]);

        // Duplicate apply for the same activity is blocked.
        Livewire::test(CreateHouseActivityRegistration::class)
            ->fillForm([
                'house_activity_id' => $activity->id,
                'student_id' => $student->id,
            ])
            ->call('create');

        $this->assertSame(
            1,
            HouseActivityRegistration::query()->where('house_activity_id', $activity->id)->where('student_id', $student->id)->count(),
        );
    }

    public function test_the_assigned_incharge_can_approve_but_an_unrelated_teacher_cannot(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        [$incharge, $otherTeacher] = User::query()->where('user_type', 'teacher')->take(2)->get();

        $house = House::query()->create(['school_id' => $schoolAdmin->school_id, 'name' => 'Yellow House']);
        $student = $this->createStudent($schoolAdmin, 'ADM-0002', $incharge->branch_id);
        $student->update(['house_id' => $house->id]);

        $activity = HouseActivity::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Inter-House Quiz',
            'category' => 'debate',
            'status' => 'registration_open',
        ]);

        HouseActivityAssignment::query()->create([
            'house_activity_id' => $activity->id,
            'house_id' => $house->id,
            'incharge_user_id' => $incharge->id,
        ]);

        $registration = HouseActivityRegistration::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $student->branch_id,
            'house_activity_id' => $activity->id,
            'house_id' => $house->id,
            'student_id' => $student->id,
            'status' => 'applied',
        ]);

        $this->actingAs($otherTeacher);
        Livewire::test(ListHouseActivityRegistrations::class)
            ->callTableAction('approve', $registration);

        $this->assertSame('applied', $registration->fresh()->status);

        $this->actingAs($incharge);
        Livewire::test(ListHouseActivityRegistrations::class)
            ->callTableAction('approve', $registration, data: ['remarks' => 'Good player'])
            ->assertHasNoActionErrors();

        $this->assertSame('approved', $registration->fresh()->status);
    }

    public function test_approval_is_blocked_once_the_house_reaches_its_participant_cap(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $incharge = User::query()->where('user_type', 'teacher')->firstOrFail();

        $house = House::query()->create(['school_id' => $schoolAdmin->school_id, 'name' => 'Orange House']);
        $students = collect([
            $this->createStudent($schoolAdmin, 'ADM-0003', $incharge->branch_id),
            $this->createStudent($schoolAdmin, 'ADM-0004', $incharge->branch_id),
        ]);
        $students->each(fn (Student $student) => $student->update(['house_id' => $house->id]));

        $activity = HouseActivity::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Inter-House Chess',
            'category' => 'other',
            'status' => 'registration_open',
            'max_participants_per_house' => 1,
        ]);

        HouseActivityAssignment::query()->create([
            'house_activity_id' => $activity->id,
            'house_id' => $house->id,
            'incharge_user_id' => $incharge->id,
        ]);

        $registrations = $students->map(fn (Student $student) => HouseActivityRegistration::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $student->branch_id,
            'house_activity_id' => $activity->id,
            'house_id' => $house->id,
            'student_id' => $student->id,
            'status' => 'applied',
        ]));

        $this->actingAs($incharge);
        Livewire::test(ListHouseActivityRegistrations::class)
            ->callTableAction('approve', $registrations[0]);

        $this->assertSame('approved', $registrations[0]->fresh()->status);

        Livewire::test(ListHouseActivityRegistrations::class)
            ->callTableAction('approve', $registrations[1]);

        $this->assertSame('applied', $registrations[1]->fresh()->status);
    }
}
