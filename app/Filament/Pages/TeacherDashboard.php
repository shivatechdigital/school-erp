<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Filament\Pages\Concerns\HasTeacherTimetableGrid;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Homework\HomeworkResource;
use App\Filament\Pages\TeacherMarksEntry;
use App\Models\AttendanceAccessGrant;
use App\Models\ExamSchedule;
use App\Models\Exam;
use App\Models\GradingPolicy;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Mark;
use App\Models\MarkApprovalRequest;
use App\Models\Section;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Timetable;
use App\Models\TimetableExchangeRequest;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TeacherDashboard extends Page
{
    use HasTeacherQuickActions;
    use HasTeacherTimetableGrid;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Teacher Dashboard';

    protected static ?string $title = 'Teacher Dashboard';

    protected string $view = 'filament.pages.teacher-dashboard';

    public string $range = 'today';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->delegateAttendanceAction(),
            $this->requestExchangeAction(),
            $this->requestLeaveAction(),
            $this->addStudentNoteAction(),
            $this->createExamPaperAction(),
            $this->publishNoticeAction(),
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $sections = Section::query()->where('class_teacher_id', $user->id)->where('is_active', true)->get();
        $sectionIds = $sections->modelKeys();
        $leaveTypes = LeaveType::query()->where('school_id', $user->school_id)->where('is_active', true)->get();
        $approvedLeaveDays = Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('from_date', now()->year)
            ->sum('total_days');

        $attendanceCounts = StudentAttendance::query()
            ->whereDate('date', today())
            ->whereIn('section_id', $sectionIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestExam = Exam::query()->where('school_id', $user->school_id)->where('status', 'completed')->latest('end_date')->first();
        $classResults = collect();
        if ($latestExam && $sectionIds !== []) {
            $classIds = $sections->pluck('class_id')->unique()->values();
            $subjectCounts = ExamSchedule::query()->where('exam_id', $latestExam->id)
                ->whereIn('class_id', $classIds)
                ->selectRaw('class_id, count(*) as total')
                ->groupBy('class_id')->pluck('total', 'class_id');
            $policy = GradingPolicy::query()->where('school_id', $user->school_id)->where('branch_id', $user->branch_id)->first();
            $marksByStudent = Mark::query()->with(['student.section', 'subject'])
                ->where('exam_id', $latestExam->id)
                ->whereHas('student', fn (Builder $query) => $query->whereIn('section_id', $sectionIds))
                ->get()->groupBy('student_id');
            $classResults = Student::query()->with('section')
                ->whereIn('section_id', $sectionIds)
                ->where('status', 'active')->orderBy('section_id')->orderBy('roll_no')
                ->get()->map(function (Student $student) use ($subjectCounts, $marksByStudent, $policy) {
                    $marks = $marksByStudent->get($student->id, collect());
                    $failedSubjects = $marks->filter(fn (Mark $mark): bool => $mark->result === 'fail'
                        || ($policy && $mark->result !== 'absent' && $mark->result !== 'withheld'
                            && (float) $mark->percentage < (float) $policy->subject_pass_percentage));
                    $percentage = $marks->sum('max_marks') > 0
                        ? ($marks->sum('total_marks') / $marks->sum('max_marks')) * 100
                        : 0;
                    $subjectCount = (int) ($subjectCounts[$student->class_id] ?? 0);
                    $complete = $subjectCount > 0 && $marks->count() >= $subjectCount;

                    return [
                        'student' => $student,
                        'complete' => $complete,
                        'passed' => $complete
                            && $failedSubjects->isEmpty()
                            && (! $policy || $percentage >= (float) $policy->overall_pass_percentage),
                        'failed_subjects' => $failedSubjects->pluck('subject.name')->filter()->values(),
                    ];
                });
        }

        return [
            'todayTimetable' => Timetable::query()
                ->with(['schoolClass', 'section', 'subject', 'substituteFor'])
                ->where('teacher_id', $user->id)
                ->where('day_of_week', strtolower(now()->format('l')))
                ->orderBy('start_time')
                ->get(),
            'sections' => $sections,
            'classStudents' => Student::query()->with(['section', 'guardians', 'notes' => fn ($query) => $query->latest()->limit(3)])
                ->whereIn('section_id', $sectionIds)->where('status', 'active')->orderBy('section_id')->orderBy('roll_no')->get(),
            'attendanceCounts' => $attendanceCounts,
            'latestExam' => $latestExam,
            'classResults' => $classResults,
            'staffAttendance' => StaffAttendance::query()->where('user_id', $user->id)->whereDate('date', today())->first(),
            'pendingLeaves' => Leave::query()->where('user_id', $user->id)->where('status', 'pending')->count(),
            'leaveBalance' => max(0, (int) $leaveTypes->sum('max_days') - (int) $approvedLeaveDays),
            'incomingExchanges' => TimetableExchangeRequest::query()
                ->with(['requester', 'requesterTimetable.schoolClass', 'requesterTimetable.section', 'requesterTimetable.subject'])
                ->where('recipient_id', $user->id)->where('status', 'pending')->latest()->get(),
            'outgoingExchanges' => TimetableExchangeRequest::query()
                ->with('recipient')->where('requester_id', $user->id)->where('status', 'pending')->latest()->get(),
            'markApprovals' => MarkApprovalRequest::query()
                ->with(['requester', 'student', 'examSchedule.exam', 'examSchedule.subject'])
                ->where('assigned_teacher_id', $user->id)->where('status', 'pending')->latest()->get(),
            'activeGrants' => AttendanceAccessGrant::query()
                ->with(['section.class', 'teacher'])
                ->where('granted_by', $user->id)->whereNull('revoked_at')->where('valid_until', '>', now())
                ->latest()->get(),
            'links' => [
                'attendance' => AttendanceResource::getUrl('bulk'),
                'homework' => HomeworkResource::getUrl('index'),
                'marks' => TeacherMarksEntry::getUrl(),
            ],
        ] + $this->timetableGridData();
    }

    public function approveMarkRequest(int $requestId): void
    {
        $approval = MarkApprovalRequest::query()
            ->whereKey($requestId)->where('assigned_teacher_id', auth()->id())->where('status', 'pending')->firstOrFail();

        DB::transaction(function () use ($approval): void {
            $schedule = ExamSchedule::query()->findOrFail($approval->exam_schedule_id);
            $mark = Mark::query()->whereKey($approval->existing_mark_id)->first();
            $mark ??= new Mark;
            $mark->fill([
                'school_id' => $approval->school_id,
                'exam_id' => $schedule->exam_id,
                'exam_schedule_id' => $schedule->id,
                'student_id' => $approval->student_id,
                'class_id' => $approval->class_id,
                'subject_id' => $approval->subject_id,
                'theory_marks' => $approval->theory_marks,
                'practical_marks' => $approval->practical_marks,
                'max_marks' => $schedule->max_marks,
                'pass_marks' => $schedule->pass_marks,
                'remark' => $approval->remark,
                'entered_by' => auth()->id(),
                'entered_at' => now(),
            ]);
            $policy = GradingPolicy::query()->where('school_id', $approval->school_id)
                ->where('branch_id', $approval->branch_id)->first();
            $mark->calculateResult($policy ? (float) $policy->subject_pass_percentage : null);
            $mark->save();

            $approval->update(['status' => 'approved', 'responded_at' => now()]);
        });

        Notification::make()->success()->title('Marks approved and saved')->send();
    }

    public function rejectMarkRequest(int $requestId): void
    {
        MarkApprovalRequest::query()
            ->whereKey($requestId)->where('assigned_teacher_id', auth()->id())->where('status', 'pending')
            ->update(['status' => 'rejected', 'responded_at' => now()]);

        Notification::make()->success()->title('Marks entry rejected')->send();
    }

}
