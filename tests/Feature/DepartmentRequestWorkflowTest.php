<?php

namespace Tests\Feature;

use App\Filament\Resources\DepartmentRequests\Pages\CreateDepartmentRequest;
use App\Filament\Resources\DepartmentRequests\Pages\ListDepartmentRequests;
use App\Models\Department;
use App\Models\DepartmentRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartmentRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_a_staff_request_goes_to_the_department_head_who_can_resolve_it(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        [$head, $raiser] = User::query()->where('user_type', 'teacher')->take(2)->get();

        $department = Department::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Science',
            'head_user_id' => $head->id,
        ]);
        $raiser->update(['department_id' => $department->id]);

        $this->actingAs($raiser);
        Livewire::test(CreateDepartmentRequest::class)
            ->fillForm([
                'type' => 'issue',
                'title' => 'Microscope not working',
                'description' => 'The lab microscope lens is cracked.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $request = DepartmentRequest::query()->where('title', 'Microscope not working')->firstOrFail();
        $this->assertSame('pending_head', $request->status);
        $this->assertSame($department->id, $request->department_id);
        $this->assertSame($raiser->id, $request->raised_by);
        $this->assertSame(1, $head->fresh()->unreadNotifications()->count());

        $this->actingAs($head);
        Livewire::test(ListDepartmentRequests::class)
            ->callTableAction('resolveByHead', $request, data: ['head_remarks' => 'Fixed by lab staff.'])
            ->assertHasNoActionErrors();

        $this->assertSame('resolved_by_head', $request->fresh()->status);
        $this->assertSame(1, $raiser->fresh()->unreadNotifications()->count());
    }

    public function test_a_head_can_escalate_a_request_and_the_admin_can_resolve_it(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        [$head, $raiser] = User::query()->where('user_type', 'teacher')->take(2)->get();

        $department = Department::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Science',
            'head_user_id' => $head->id,
        ]);
        $raiser->update(['department_id' => $department->id]);

        $request = DepartmentRequest::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $raiser->branch_id,
            'department_id' => $department->id,
            'raised_by' => $raiser->id,
            'type' => 'item_request',
            'title' => 'Need a new projector',
            'description' => 'Classroom projector is broken beyond repair.',
            'status' => 'pending_head',
        ]);

        $this->actingAs($head);
        Livewire::test(ListDepartmentRequests::class)
            ->callTableAction('escalateToAdmin', $request, data: ['head_remarks' => 'Needs budget approval.'])
            ->assertHasNoActionErrors();

        $this->assertSame('pending_admin', $request->fresh()->status);
        $this->assertSame(1, $schoolAdmin->fresh()->unreadNotifications()->count());

        $this->actingAs($schoolAdmin);
        Livewire::test(ListDepartmentRequests::class)
            ->callTableAction('resolveByAdmin', $request, data: ['admin_remarks' => 'Approved, ordering now.'])
            ->assertHasNoActionErrors();

        $this->assertSame('resolved', $request->fresh()->status);
        $this->assertSame(1, $raiser->fresh()->unreadNotifications()->count());
    }

    public function test_a_request_routes_directly_to_admin_when_the_department_has_no_head(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $raiser = User::query()->where('user_type', 'teacher')->firstOrFail();

        $department = Department::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Commerce',
        ]);
        $raiser->update(['department_id' => $department->id]);

        $this->actingAs($raiser);
        Livewire::test(CreateDepartmentRequest::class)
            ->fillForm([
                'type' => 'report',
                'title' => 'Monthly attendance report',
                'description' => 'Sharing the department attendance summary.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $request = DepartmentRequest::query()->where('title', 'Monthly attendance report')->firstOrFail();
        $this->assertSame('pending_admin', $request->status);
        $this->assertSame(1, $schoolAdmin->fresh()->unreadNotifications()->count());
    }

    public function test_staff_only_see_their_own_requests_while_the_head_sees_the_whole_department(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        [$head, $teacherA, $teacherB] = User::query()->where('user_type', 'teacher')->take(3)->get();

        $department = Department::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Science',
            'head_user_id' => $head->id,
        ]);
        $teacherA->update(['department_id' => $department->id]);
        $teacherB->update(['department_id' => $department->id]);

        $requestA = DepartmentRequest::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $teacherA->branch_id,
            'department_id' => $department->id,
            'raised_by' => $teacherA->id,
            'type' => 'issue',
            'title' => 'Teacher A issue',
            'description' => 'Detail A',
            'status' => 'pending_head',
        ]);

        $requestB = DepartmentRequest::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'branch_id' => $teacherB->branch_id,
            'department_id' => $department->id,
            'raised_by' => $teacherB->id,
            'type' => 'issue',
            'title' => 'Teacher B issue',
            'description' => 'Detail B',
            'status' => 'pending_head',
        ]);

        $this->actingAs($teacherA);
        Livewire::test(ListDepartmentRequests::class)
            ->assertCanSeeTableRecords([$requestA])
            ->assertCanNotSeeTableRecords([$requestB]);

        $this->actingAs($head);
        Livewire::test(ListDepartmentRequests::class)
            ->assertCanSeeTableRecords([$requestA, $requestB]);
    }
}
