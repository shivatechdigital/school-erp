<?php

namespace Tests\Feature;

use App\Filament\Resources\AcademicDepartments\Pages\CreateAcademicDepartment;
use App\Filament\Resources\SubjectResource\Pages\CreateSubject;
use App\Models\AcademicDepartment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicDepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_school_admin_can_create_an_academic_department(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        Livewire::test(CreateAcademicDepartment::class)
            ->fillForm([
                'name' => 'Science',
                'code' => 'SCI',
                'description' => 'Physics, Chemistry, Biology',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('academic_departments', [
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Science',
            'code' => 'SCI',
        ]);
    }

    public function test_subject_can_be_assigned_to_a_department_or_left_common(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        $department = AcademicDepartment::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Languages',
            'code' => 'LANG',
        ]);

        Livewire::test(CreateSubject::class)
            ->fillForm([
                'name' => 'Hindi',
                'type' => 'theory',
                'academic_department_id' => $department->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subjects', [
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Hindi',
            'academic_department_id' => $department->id,
        ]);

        Livewire::test(CreateSubject::class)
            ->fillForm([
                'name' => 'General Knowledge',
                'type' => 'theory',
                'academic_department_id' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subjects', [
            'school_id' => $schoolAdmin->school_id,
            'name' => 'General Knowledge',
            'academic_department_id' => null,
        ]);
    }
}
