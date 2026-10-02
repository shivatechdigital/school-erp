<?php

namespace App\Filament\Pages;

use App\Models\MarkAudit;
use App\Models\AttendanceAccessGrant;
use App\Models\StudentAttendanceAudit;
use App\Models\TimetableExchangeRequest;
use Filament\Pages\Page;

class TeacherWorkflowAudit extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Teacher Workflow Audit';

    protected static ?string $title = 'Teacher Workflow Audit';

    protected string $view = 'filament.pages.teacher-workflow-audit';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true);
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->user_type === 'super_admin';

        return [
            'attendanceGrants' => AttendanceAccessGrant::query()
                ->with(['section.class', 'teacher', 'grantedBy'])
                ->when(! $isSuperAdmin, fn ($query) => $query->where('school_id', $user->school_id)
                    ->when($user->branch_id, fn ($branchQuery) => $branchQuery->where('branch_id', $user->branch_id)))
                ->latest()->limit(100)->get(),
            'attendanceAudits' => StudentAttendanceAudit::query()
                ->with(['attendance.student', 'attendance.section.class', 'changedBy'])
                ->when(! $isSuperAdmin, fn ($query) => $query->whereHas('attendance', fn ($attendance) => $attendance->where('school_id', $user->school_id)
                    ->when($user->branch_id, fn ($branchQuery) => $branchQuery->where('branch_id', $user->branch_id))))
                ->latest('changed_at')->limit(100)->get(),
            'markAudits' => MarkAudit::query()
                ->with(['mark.student', 'mark.subject', 'changedBy'])
                ->when(! $isSuperAdmin, fn ($query) => $query->whereHas('mark', fn ($mark) => $mark->where('school_id', $user->school_id)
                    ->when($user->branch_id, fn ($branchQuery) => $branchQuery->whereHas('student', fn ($students) => $students->where('branch_id', $user->branch_id)))))
                ->latest('changed_at')->limit(100)->get(),
            'exchangeRequests' => TimetableExchangeRequest::query()
                ->with(['requester', 'recipient', 'requesterTimetable.schoolClass', 'requesterTimetable.section', 'requesterTimetable.subject'])
                ->when(! $isSuperAdmin, fn ($query) => $query->where('school_id', $user->school_id)
                    ->when($user->branch_id, fn ($branchQuery) => $branchQuery->whereHas('requesterTimetable', fn ($timetable) => $timetable->where('branch_id', $user->branch_id))))
                ->latest()->limit(100)->get(),
        ];
    }
}