<?php

namespace Tests\Feature;

use App\Filament\Pages\GradingPolicySettings;
use App\Filament\Pages\TeacherDashboard;
use App\Filament\Pages\TeacherMarksEntry;
use App\Filament\AdminDashboard;
use App\Filament\Resources\AcademicYearResource;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Homework\HomeworkResource;
use App\Filament\Resources\SchoolClassResource;
use App\Filament\Resources\StudentResource;
use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherWorkflowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_teacher_and_admin_dashboard_access_are_separated(): void
    {
        $teacher = User::query()->where('user_type', 'teacher')->firstOrFail();
        $principal = User::query()->where('email', 'principal@demo.com')->firstOrFail();

        $this->actingAs($teacher);
        $this->assertTrue(TeacherDashboard::canAccess());
        $this->assertTrue(TeacherMarksEntry::canAccess());
        $this->assertTrue(AdminDashboard::canAccess());
        $this->assertSame([], AdminDashboard::getNavigationItems());
        $this->assertFalse(AcademicYearResource::canViewAny());
        $this->assertFalse(SchoolClassResource::canViewAny());
        $this->assertFalse(StudentResource::canViewAny());
        $this->assertTrue(HomeworkResource::canViewAny());
        $this->assertFalse(GradingPolicySettings::canAccess());

        $this->actingAs($principal);
        $this->assertFalse(TeacherDashboard::canAccess());
        $this->assertFalse(TeacherMarksEntry::canAccess());
        $this->assertTrue(AdminDashboard::canAccess());
        $this->assertTrue(GradingPolicySettings::canAccess());
    }

    public function test_teacher_attendance_records_are_limited_to_assigned_sections(): void
    {
        $teacher = User::query()->where('user_type', 'teacher')->firstOrFail();
        $this->actingAs($teacher);

        $assignedSections = Section::query()->where('class_teacher_id', $teacher->id)->get();
        $this->assertNotEmpty($assignedSections);

        $visibleAttendance = AttendanceResource::getEloquentQuery()->with('section')->get();
        $this->assertTrue($visibleAttendance->every(fn (StudentAttendance $attendance): bool =>
            $attendance->section?->class_teacher_id === $teacher->id));

        $this->assertFalse(AttendanceResource::getEloquentQuery()
            ->whereDoesntHave('section', fn ($query) => $query->where('class_teacher_id', $teacher->id))
            ->exists());
    }
}