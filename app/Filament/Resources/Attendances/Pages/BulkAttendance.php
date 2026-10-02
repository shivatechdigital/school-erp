<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\AttendanceAccessGrant;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceAudit;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class BulkAttendance extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    public const STATUSES = ['present', 'absent', 'late', 'half_day', 'leave'];

    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.resources.attendances.pages.bulk-attendance';

    protected static ?string $title = 'Mark Attendance';

    public ?array $data = [];

    public array $studentList = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Select Class & Date')
                    ->schema([
                        Select::make('class_id')
                            ->label('Class')
                            ->options(fn (): array => SchoolClass::query()
                                ->where('is_active', true)
                                ->when(auth()->user()?->user_type === 'teacher', fn ($query) => $query->whereHas('sections', fn ($sections) => $this->scopeTeacherSections($sections)))
                                ->orderBy('sort_order')
                                ->pluck('name', 'id')
                                ->all())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (callable $set): void {
                                $set('section_id', null);
                                $this->studentList = [];
                            }),

                        Select::make('section_id')
                            ->label('Section')
                            ->options(function (Get $get): array {
                                $classId = $get('class_id');
                                if (! $classId) {
                                    return [];
                                }

                                return Section::query()
                                    ->where('class_id', $classId)
                                    ->where('is_active', true)
                                    ->when(auth()->user()?->user_type === 'teacher', fn ($query) => $this->scopeTeacherSections($query))
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadStudents()),

                        DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadStudents()),
                    ])->columns(3),

                FormSection::make('Quick Actions')
                    ->schema([
                        Actions::make([
                            Action::make('all_present')
                                ->label('✅ All Present')
                                ->color('success')
                                ->icon('heroicon-o-check')
                                ->action(fn () => $this->markAll('present')),

                            Action::make('all_absent')
                                ->label('❌ All Absent')
                                ->color('danger')
                                ->icon('heroicon-o-x-mark')
                                ->action(fn () => $this->markAll('absent')),
                        ]),
                    ])
                    ->hidden(fn (): bool => empty($this->studentList)),
            ])
            ->statePath('data');
    }

    public function loadStudents(): void
    {
        $sectionId = $this->data['section_id'] ?? null;
        $date = $this->data['date'] ?? now()->toDateString();

        if (! $sectionId || ! $this->canManageSection((int) $sectionId)) {
            $this->studentList = [];

            return;
        }

        $students = Student::query()
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->orderBy('roll_no')
            ->get();

        $existing = StudentAttendance::query()
            ->where('section_id', $sectionId)
            ->whereDate('date', $date)
            ->whereNull('period_number')
            ->pluck('status', 'student_id');

        $this->studentList = $students->map(fn (Student $s): array => [
            'id' => $s->id,
            'name' => $s->full_name,
            'roll_no' => $s->roll_no ?? '-',
            'status' => $existing[$s->id] ?? 'present',
        ])->values()->toArray();
    }

    public function markAll(string $status): void
    {
        foreach ($this->studentList as $key => $student) {
            $this->studentList[$key]['status'] = $status;
        }
    }

    public function saveAttendance(): void
    {
        if (empty($this->studentList)) {
            Notification::make()
                ->danger()
                ->title('No students found!')
                ->body('Please select class and section first.')
                ->send();

            return;
        }

        $data = $this->form->getState();

        if (! $this->canManageSection((int) $data['section_id'])) {
            Notification::make()
                ->danger()
                ->title('You cannot mark attendance for this section.')
                ->send();

            return;
        }

        // Client-sent IDs are untrusted: only students of the selected section are saved.
        $statuses = collect($this->studentList)
            ->mapWithKeys(fn (array $s): array => [(int) $s['id'] => in_array($s['status'], self::STATUSES, true) ? $s['status'] : 'present']);

        $students = Student::query()
            ->where('section_id', $data['section_id'])
            ->whereIn('id', $statuses->keys())
            ->get();

        $accessGrant = $this->accessGrantForSection((int) $data['section_id']);

        DB::transaction(function () use ($data, $students, $statuses, $accessGrant): void {
            foreach ($students as $student) {
                $attendance = StudentAttendance::query()->firstOrNew([
                    'student_id' => $student->id,
                    'date' => $data['date'],
                    'period_number' => null,
                ]);
                $oldStatus = $attendance->exists ? $attendance->status : null;
                $newStatus = $statuses[$student->id];

                $attendance->fill([
                    'school_id' => $student->school_id,
                    'branch_id' => $student->branch_id,
                    'academic_year_id' => $student->academic_year_id,
                    'class_id' => $student->class_id,
                    'section_id' => $student->section_id,
                    'status' => $newStatus,
                    'marked_by' => auth()->id(),
                    'marked_at' => now(),
                ])->save();

                if ($oldStatus !== $newStatus) {
                    StudentAttendanceAudit::query()->create([
                        'student_attendance_id' => $attendance->id,
                        'changed_by' => auth()->id(),
                        'access_grant_id' => $accessGrant?->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_at' => now(),
                    ]);
                }
            }
        });

        $present = $statuses->filter(fn (string $s): bool => $s === 'present')->count();
        $absent = $statuses->filter(fn (string $s): bool => $s === 'absent')->count();
        $total = $students->count();

        Notification::make()
            ->success()
            ->title('Attendance Saved Successfully!')
            ->body("📊 {$present} Present | {$absent} Absent | Total: {$total}")
            ->duration(5000)
            ->send();
    }

    private function canManageSection(int $sectionId): bool
    {
        $section = Section::query()
            ->whereKey($sectionId)
            ->where('class_id', $this->data['class_id'] ?? null)
            ->first();

        if (! $section) {
            return false;
        }

        if (auth()->user()?->user_type !== 'teacher' || $section->class_teacher_id === auth()->id()) {
            return true;
        }

        return $this->accessGrantForSection($sectionId) !== null;
    }

    private function scopeTeacherSections($query)
    {
        return $query->where(function ($sections): void {
            $sections->where('class_teacher_id', auth()->id())
                ->orWhereIn('id', AttendanceAccessGrant::query()
                    ->where('teacher_id', auth()->id())
                    ->whereNull('revoked_at')
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>', now())
                    ->select('section_id'));
        });
    }

    private function accessGrantForSection(int $sectionId): ?AttendanceAccessGrant
    {
        if (auth()->user()?->user_type !== 'teacher') {
            return null;
        }

        return AttendanceAccessGrant::query()
            ->where('section_id', $sectionId)
            ->where('teacher_id', auth()->id())
            ->whereNull('revoked_at')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>', now())
            ->latest('valid_until')
            ->first();
    }
}
