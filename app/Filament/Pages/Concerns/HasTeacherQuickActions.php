<?php

namespace App\Filament\Pages\Concerns;

use App\Models\AttendanceAccessGrant;
use App\Models\ExamPaper;
use App\Models\ExamSchedule;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Notice;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Timetable;
use App\Models\TimetableExchangeRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

trait HasTeacherQuickActions
{
    public function acceptExchange(int $requestId): void
    {
        $exchange = TimetableExchangeRequest::query()
            ->whereKey($requestId)->where('recipient_id', auth()->id())->where('status', 'pending')->firstOrFail();

        try {
            DB::transaction(function () use ($exchange): void {
                $exchange = TimetableExchangeRequest::query()->lockForUpdate()->findOrFail($exchange->id);
                $requesterSlot = Timetable::query()->lockForUpdate()->findOrFail($exchange->requester_timetable_id);
                $recipientSlot = Timetable::query()->lockForUpdate()->findOrFail($exchange->recipient_timetable_id);

                if ($exchange->status !== 'pending'
                    || $requesterSlot->teacher_id !== $exchange->requester_id
                    || $recipientSlot->teacher_id !== $exchange->recipient_id) {
                    throw ValidationException::withMessages(['exchange' => 'The timetable changed after this request was sent.']);
                }

                if ($this->hasTeachingConflict((int) $exchange->recipient_id, $requesterSlot, $requesterSlot->id, $recipientSlot->id)
                    || $this->hasTeachingConflict((int) $exchange->requester_id, $recipientSlot, $requesterSlot->id, $recipientSlot->id)) {
                    throw ValidationException::withMessages(['exchange' => 'Accepting this exchange would create an overlapping class.']);
                }

                $requesterId = $requesterSlot->teacher_id;
                $recipientId = $recipientSlot->teacher_id;

                $requesterSlot->update([
                    'teacher_id' => $recipientId,
                    'substitute_for_id' => $requesterId,
                    'exchange_request_id' => $exchange->id,
                ]);
                $recipientSlot->update([
                    'teacher_id' => $requesterId,
                    'substitute_for_id' => $recipientId,
                    'exchange_request_id' => $exchange->id,
                ]);
                $exchange->update(['status' => 'accepted', 'responded_at' => now()]);
            });
        } catch (ValidationException $exception) {
            Notification::make()->danger()->title('Exchange could not be accepted')->body(collect($exception->errors())->flatten()->first())->send();

            return;
        }

        Notification::make()->success()->title('Timetable exchange accepted')->send();
    }

    public function rejectExchange(int $requestId): void
    {
        $updated = TimetableExchangeRequest::query()
            ->whereKey($requestId)->where('recipient_id', auth()->id())->where('status', 'pending')
            ->update(['status' => 'rejected', 'responded_at' => now()]);

        if ($updated) {
            Notification::make()->success()->title('Exchange request declined')->send();
        }
    }

    public function revokeAttendanceGrant(int $grantId): void
    {
        $grant = AttendanceAccessGrant::query()
            ->whereKey($grantId)->where('granted_by', auth()->id())->whereNull('revoked_at')->firstOrFail();
        $grant->update(['revoked_at' => now()]);

        Notification::make()->success()->title('Attendance access revoked')->send();
    }

    protected function delegateAttendanceAction(): Action
    {
        return Action::make('delegateAttendance')
            ->label('Assign Attendance Cover')
            ->icon('heroicon-o-user-plus')
            ->form([
                Select::make('section_id')
                    ->label('Your class section')
                    ->options(fn (): array => Section::query()->where('class_teacher_id', auth()->id())->with('class')->get()->mapWithKeys(
                        fn (Section $section): array => [$section->id => "{$section->class?->name} - {$section->name}"]
                    )->all())
                    ->required(),
                Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(fn (): array => User::query()->where('user_type', 'teacher')->where('status', 'active')
                        ->where('school_id', auth()->user()->school_id)
                        ->where('branch_id', auth()->user()->branch_id)
                        ->whereKeyNot(auth()->id())
                        ->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->required(),
                Select::make('duration_hours')
                    ->label('Access duration')
                    ->options(['1' => '1 hour', '2' => '2 hours', '24' => '1 day', '720' => '30 days'])
                    ->required(),
                Textarea::make('reason')
                    ->label('Reason for delegating attendance')
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (array $data): void {
                $section = Section::query()->whereKey($data['section_id'])->where('class_teacher_id', auth()->id())->firstOrFail();
                $teacher = User::query()->whereKey($data['teacher_id'])->where('user_type', 'teacher')
                    ->where('school_id', auth()->user()->school_id)
                    ->where('branch_id', $section->branch_id)->firstOrFail();
                $hours = (int) $data['duration_hours'];
                abort_unless(in_array($hours, [1, 2, 24, 720], true), 422);

                AttendanceAccessGrant::query()->create([
                    'school_id' => $section->school_id,
                    'branch_id' => $section->branch_id,
                    'section_id' => $section->id,
                    'teacher_id' => $teacher->id,
                    'granted_by' => auth()->id(),
                    'reason' => $data['reason'],
                    'valid_from' => now(),
                    'valid_until' => now()->addHours($hours),
                ]);

                Notification::make()->success()->title('Temporary attendance access assigned')->send();
            });
    }

    protected function requestExchangeAction(): Action
    {
        return Action::make('requestExchange')
            ->label('Request Timetable Exchange')
            ->icon('heroicon-o-arrows-right-left')
            ->form([
                Select::make('requester_timetable_id')
                    ->label('Your class period')
                    ->options(fn (): array => $this->timetableOptions(Timetable::query()->where('teacher_id', auth()->id())->whereNull('exchange_request_id')->where('is_break', false)))
                    ->live()->required(),
                Select::make('recipient_timetable_id')
                    ->label('Teacher/period to exchange with')
                    ->options(function (Get $get): array {
                        $ownSlot = Timetable::query()->find($get('requester_timetable_id'));
                        if (! $ownSlot) {
                            return [];
                        }

                        return $this->timetableOptions(Timetable::query()
                            ->where('teacher_id', '!=', auth()->id())
                            ->where('day_of_week', $ownSlot->day_of_week)
                            ->where('start_time', $ownSlot->start_time)
                            ->where('end_time', $ownSlot->end_time)
                            ->whereNull('exchange_request_id')->where('is_break', false));
                    })
                    ->searchable()->required(),
                Textarea::make('reason')->label('Reason for exchange')->required()->maxLength(2000),
            ])
            ->action(function (array $data): void {
                $ownSlot = Timetable::query()->whereKey($data['requester_timetable_id'])
                    ->where('teacher_id', auth()->id())->whereNull('exchange_request_id')->where('is_break', false)->firstOrFail();
                $targetSlot = Timetable::query()->with('teacher')->whereKey($data['recipient_timetable_id'])
                    ->where('teacher_id', '!=', auth()->id())->where('day_of_week', $ownSlot->day_of_week)
                    ->where('start_time', $ownSlot->start_time)->where('end_time', $ownSlot->end_time)
                    ->whereNull('exchange_request_id')->where('is_break', false)->firstOrFail();

                TimetableExchangeRequest::query()->create([
                    'school_id' => $ownSlot->school_id,
                    'requester_id' => auth()->id(),
                    'recipient_id' => $targetSlot->teacher_id,
                    'requester_timetable_id' => $ownSlot->id,
                    'recipient_timetable_id' => $targetSlot->id,
                    'reason' => $data['reason'],
                ]);

                Notification::make()->success()->title('Exchange request sent')->send();
            });
    }

    protected function requestLeaveAction(): Action
    {
        return Action::make('requestLeave')
            ->label('Apply for Leave')
            ->icon('heroicon-o-calendar')
            ->form([
                Select::make('leave_type_id')
                    ->label('Leave type')
                    ->options(fn (): array => LeaveType::query()->where('school_id', auth()->user()->school_id)
                        ->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->required(),
                DatePicker::make('from_date')->label('From')->required()->native(false),
                DatePicker::make('to_date')->label('To')->required()->native(false)->afterOrEqual('from_date'),
                Textarea::make('reason')->required()->maxLength(2000),
            ])
            ->action(function (array $data): void {
                $from = now()->parse($data['from_date'])->startOfDay();
                $to = now()->parse($data['to_date'])->startOfDay();
                Leave::query()->create([
                    'school_id' => auth()->user()->school_id,
                    'user_id' => auth()->id(),
                    'leave_type_id' => LeaveType::query()->where('school_id', auth()->user()->school_id)->findOrFail($data['leave_type_id'])->id,
                    'from_date' => $from,
                    'to_date' => $to,
                    'total_days' => $from->diffInDays($to) + 1,
                    'reason' => $data['reason'],
                    'status' => 'pending',
                ]);

                Notification::make()->success()->title('Leave request submitted')->send();
            });
    }

    protected function addStudentNoteAction(): Action
    {
        return Action::make('addStudentNote')
            ->label('Add Student Note')
            ->icon('heroicon-o-pencil-square')
            ->form([
                Select::make('student_id')
                    ->label('Student')
                    ->options(fn (): array => Student::query()->whereHas('section', fn (Builder $query) => $query->where('class_teacher_id', auth()->id()))
                        ->orderBy('first_name')->get()->mapWithKeys(fn (Student $student): array => [$student->id => "{$student->full_name} ({$student->admission_no})"])->all())
                    ->searchable()->required(),
                Textarea::make('note')->required()->maxLength(5000),
            ])
            ->action(function (array $data): void {
                $student = Student::query()->whereKey($data['student_id'])
                    ->whereHas('section', fn (Builder $query) => $query->where('class_teacher_id', auth()->id()))
                    ->firstOrFail();
                StudentNote::query()->create([
                    'school_id' => $student->school_id,
                    'branch_id' => $student->branch_id,
                    'student_id' => $student->id,
                    'author_id' => auth()->id(),
                    'note' => $data['note'],
                    'visibility' => 'class_teacher',
                ]);

                Notification::make()->success()->title('Student note saved')->send();
            });
    }

    protected function createExamPaperAction(): Action
    {
        return Action::make('createExamPaper')
            ->label('Upload Exam Paper')
            ->icon('heroicon-o-document-arrow-up')
            ->form([
                Select::make('exam_schedule_id')
                    ->label('Exam / class / subject')
                    ->options(fn (): array => ExamSchedule::query()->where('school_id', auth()->user()->school_id)
                        ->whereHas('class', fn (Builder $class) => $class->whereHas('subjects', fn (Builder $subjects) => $subjects
                            ->whereColumn('class_subject.class_id', 'exam_schedules.class_id')
                            ->whereColumn('class_subject.subject_id', 'exam_schedules.subject_id')
                            ->where('class_subject.teacher_id', auth()->id())))
                        ->with(['exam', 'class', 'subject'])->latest('exam_date')->get()
                        ->mapWithKeys(fn (ExamSchedule $schedule): array => [$schedule->id => "{$schedule->exam?->name} · {$schedule->class?->name} · {$schedule->subject?->name}"])->all())
                    ->searchable()->required(),
                Select::make('section_id')
                    ->label('Section (optional)')
                    ->options(function (Get $get): array {
                        $schedule = ExamSchedule::query()->find($get('exam_schedule_id'));

                        return $schedule ? Section::query()->where('class_id', $schedule->class_id)->orderBy('name')->pluck('name', 'id')->all() : [];
                    })
                    ->searchable(),
                Textarea::make('title')->required()->maxLength(255),
                Textarea::make('instructions')->maxLength(3000),
                FileUpload::make('file_path')->label('Paper file')->disk('public')->directory('exam-papers')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(20480)->required(),
            ])
            ->action(function (array $data): void {
                $schedule = ExamSchedule::query()->where('school_id', auth()->user()->school_id)
                    ->whereKey($data['exam_schedule_id'])
                    ->whereHas('class', fn (Builder $class) => $class->whereHas('subjects', fn (Builder $subjects) => $subjects
                        ->whereColumn('class_subject.class_id', 'exam_schedules.class_id')
                        ->whereColumn('class_subject.subject_id', 'exam_schedules.subject_id')
                        ->where('class_subject.teacher_id', auth()->id())))
                    ->firstOrFail();
                if (! empty($data['section_id'])) {
                    Section::query()->where('class_id', $schedule->class_id)->findOrFail($data['section_id']);
                }

                ExamPaper::query()->create([
                    'school_id' => $schedule->school_id,
                    'branch_id' => auth()->user()->branch_id,
                    'exam_schedule_id' => $schedule->id,
                    'class_id' => $schedule->class_id,
                    'section_id' => $data['section_id'] ?? null,
                    'teacher_id' => auth()->id(),
                    'title' => $data['title'],
                    'instructions' => $data['instructions'] ?? null,
                    'file_path' => $data['file_path'],
                ]);

                Notification::make()->success()->title('Exam paper uploaded')->send();
            });
    }

    protected function publishNoticeAction(): Action
    {
        return Action::make('publishNotice')
            ->label('Send Class Notice')
            ->icon('heroicon-o-megaphone')
            ->form([
                Select::make('section_id')
                    ->label('Assigned class section')
                    ->options(fn (): array => Section::query()->where('class_teacher_id', auth()->id())->with('class')->get()
                        ->mapWithKeys(fn (Section $section): array => [$section->id => "{$section->class?->name} · {$section->name}"])->all())
                    ->required(),
                Textarea::make('title')->required()->maxLength(255),
                Textarea::make('content')->required()->maxLength(10000),
                Select::make('priority')->options(['normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])->default('normal')->required(),
                DatePicker::make('expiry_date')->label('Expiry date')->native(false)->afterOrEqual(today()),
            ])
            ->action(function (array $data): void {
                $section = Section::query()->whereKey($data['section_id'])->where('class_teacher_id', auth()->id())->firstOrFail();
                Notice::query()->create([
                    'school_id' => $section->school_id,
                    'branch_id' => $section->branch_id,
                    'published_by' => auth()->id(),
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'priority' => $data['priority'],
                    'target_audience' => 'specific_class',
                    'class_id' => $section->class_id,
                    'section_id' => $section->id,
                    'publish_date' => today(),
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'is_published' => true,
                    'send_notification' => true,
                ]);

                Notification::make()->success()->title('Notice published')->send();
            });
    }

    protected function timetableOptions(Builder $query): array
    {
        return $query->with(['schoolClass', 'section', 'subject', 'teacher'])->orderBy('day_of_week')->orderBy('start_time')->get()
            ->mapWithKeys(fn (Timetable $slot): array => [
                $slot->id => trim(($slot->teacher ? $slot->teacher->name.' - ' : '')."{$slot->day_of_week} {$slot->start_time} | {$slot->schoolClass?->name} {$slot->section?->name} | {$slot->subject?->name}"),
            ])->all();
    }

    protected function hasTeachingConflict(int $teacherId, Timetable $slot, int $firstIgnoreId, int $secondIgnoreId): bool
    {
        return Timetable::query()->where('teacher_id', $teacherId)
            ->where('day_of_week', $slot->day_of_week)
            ->where('start_time', '<', $slot->end_time)
            ->where('end_time', '>', $slot->start_time)
            ->whereNotIn('id', [$firstIgnoreId, $secondIgnoreId])
            ->exists();
    }
}
