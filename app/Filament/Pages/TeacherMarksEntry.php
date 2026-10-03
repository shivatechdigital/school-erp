<?php

namespace App\Filament\Pages;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\GradingPolicy;
use App\Models\Mark;
use App\Models\MarkApprovalRequest;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class TeacherMarksEntry extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Marks Entry';

    protected static ?string $title = 'Class Marks Entry';

    protected string $view = 'filament.pages.teacher-marks-entry';

    public ?array $data = [];

    public array $studentList = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('exam_id')
                    ->label('Exam')
                    ->options(fn (): array => Exam::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('class_id', null);
                        $set('section_id', null);
                        $set('subject_id', null);
                        $this->studentList = [];
                    })
                    ->required(),
                Select::make('class_id')
                    ->label('Class')
                    ->options(fn (Get $get): array => ExamSchedule::query()
                        ->where('exam_id', $get('exam_id'))
                        ->with('class')->get()->unique('class_id')
                        ->mapWithKeys(fn (ExamSchedule $schedule): array => [$schedule->class_id => $schedule->class?->name ?? ''])
                        ->all())
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('section_id', null);
                        $set('subject_id', null);
                        $this->studentList = [];
                    })
                    ->required(),
                Select::make('section_id')
                    ->label('Section')
                    ->options(fn (Get $get): array => ['all' => 'All sections'] + Section::query()
                        ->where('class_id', $get('class_id'))->where('is_active', true)
                        ->orderBy('name')->pluck('name', 'id')->all())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadStudents())
                    ->required(),
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn (Get $get): array => ExamSchedule::query()
                        ->where('exam_id', $get('exam_id'))->where('class_id', $get('class_id'))
                        ->with('subject')->get()->unique('subject_id')
                        ->mapWithKeys(fn (ExamSchedule $schedule): array => [$schedule->subject_id => $schedule->subject?->name ?? ''])
                        ->all())
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadStudents())
                    ->required(),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function loadStudents(): void
    {
        $data = $this->form->getState();
        if (! ($data['exam_id'] ?? null) || ! ($data['class_id'] ?? null) || ! ($data['subject_id'] ?? null) || ! ($data['section_id'] ?? null)) {
            $this->studentList = [];

            return;
        }

        $schedule = $this->scheduleFor($data);
        if (! $schedule) {
            $this->studentList = [];

            return;
        }

        $students = Student::query()->where('class_id', $data['class_id'])->where('status', 'active')
            ->when($data['section_id'] !== 'all', fn ($query) => $query->where('section_id', $data['section_id']))
            ->orderBy('section_id')->orderBy('roll_no')->get();
        $marks = Mark::query()->where('exam_schedule_id', $schedule->id)->whereIn('student_id', $students->modelKeys())->get()->keyBy('student_id');

        $this->studentList = $students->map(fn (Student $student): array => [
            'id' => $student->id,
            'name' => $student->full_name,
            'roll_no' => $student->roll_no ?? '-',
            'section' => $student->section?->name ?? '-',
            'theory_marks' => $marks[$student->id]?->theory_marks,
            'practical_marks' => $marks[$student->id]?->practical_marks,
            'remark' => $marks[$student->id]?->remark,
        ])->values()->all();
    }

    public function saveMarks(): void
    {
        $data = $this->form->getState();
        $schedule = $this->scheduleFor($data);
        if (! $schedule || empty($this->studentList)) {
            Notification::make()->danger()->title('Choose an exam, class, section, and subject first.')->send();

            return;
        }

        $assignedTeacherId = DB::table('class_subject')
            ->where('class_id', $schedule->class_id)
            ->where('subject_id', $schedule->subject_id)
            ->value('teacher_id');

        if (! $assignedTeacherId) {
            Notification::make()->danger()->title('No assigned subject teacher is configured for this class.')->send();

            return;
        }

        $studentIds = collect($this->studentList)->pluck('id')->map(fn ($id): int => (int) $id);
        $students = Student::query()->where('class_id', $schedule->class_id)
            ->whereIn('id', $studentIds)
            ->when($data['section_id'] !== 'all', fn ($query) => $query->where('section_id', $data['section_id']))
            ->get()->keyBy('id');

        $saved = 0;
        $approvalCount = 0;

        DB::transaction(function () use ($data, $schedule, $assignedTeacherId, $students, &$saved, &$approvalCount): void {
            foreach ($this->studentList as $entry) {
                $student = $students->get((int) ($entry['id'] ?? 0));
                if (! $student) {
                    continue;
                }

                $theory = $this->nullableMark($entry['theory_marks'] ?? null, (float) $schedule->max_marks);
                $practical = $this->nullableMark($entry['practical_marks'] ?? null, (float) $schedule->max_marks);
                if ($theory === null && $practical === null) {
                    continue;
                }

                $mark = Mark::query()->where('exam_schedule_id', $schedule->id)->where('student_id', $student->id)->first();

                if ((int) $assignedTeacherId === (int) auth()->id()) {
                    $mark ??= new Mark;
                    $mark->fill([
                        'school_id' => $schedule->school_id,
                        'exam_id' => $schedule->exam_id,
                        'exam_schedule_id' => $schedule->id,
                        'student_id' => $student->id,
                        'class_id' => $schedule->class_id,
                        'subject_id' => $schedule->subject_id,
                        'theory_marks' => $theory,
                        'practical_marks' => $practical,
                        'max_marks' => $schedule->max_marks,
                        'pass_marks' => $schedule->pass_marks,
                        'remark' => $entry['remark'] ?? null,
                        'entered_by' => auth()->id(),
                        'entered_at' => now(),
                    ]);
                    $policy = GradingPolicy::query()->where('school_id', $student->school_id)
                        ->where('branch_id', $student->branch_id)->first();
                    $mark->calculateResult($policy ? (float) $policy->subject_pass_percentage : null);
                    $mark->save();
                    $saved++;

                    continue;
                }

                MarkApprovalRequest::query()->create([
                    'school_id' => $student->school_id,
                    'branch_id' => $student->branch_id,
                    'exam_schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                    'class_id' => $schedule->class_id,
                    'subject_id' => $schedule->subject_id,
                    'existing_mark_id' => $mark?->id,
                    'requested_by' => auth()->id(),
                    'assigned_teacher_id' => $assignedTeacherId,
                    'theory_marks' => $theory,
                    'practical_marks' => $practical,
                    'remark' => $entry['remark'] ?? null,
                ]);
                $approvalCount++;
            }
        });

        $this->loadStudents();
        Notification::make()->success()->title('Marks saved or sent for approval')
            ->body("{$saved} saved directly · {$approvalCount} sent to the assigned teacher")
            ->send();
    }

    private function scheduleFor(array $data): ?ExamSchedule
    {
        return ExamSchedule::query()
            ->where('exam_id', $data['exam_id'] ?? null)
            ->where('class_id', $data['class_id'] ?? null)
            ->where('subject_id', $data['subject_id'] ?? null)
            ->first();
    }

    private function nullableMark(mixed $value, float $maximum): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (float) $value < 0 || (float) $value > $maximum) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'studentList' => "Marks must be between 0 and {$maximum}.",
            ]);
        }

        return (float) $value;
    }
}