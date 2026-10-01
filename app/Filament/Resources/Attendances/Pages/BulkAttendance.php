<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
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
                            ->options(fn (): array => SchoolClass::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id')->all())
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

        if (! $sectionId) {
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

        // Client-sent IDs are untrusted: only students of the selected section are saved.
        $statuses = collect($this->studentList)
            ->mapWithKeys(fn (array $s): array => [(int) $s['id'] => in_array($s['status'], self::STATUSES, true) ? $s['status'] : 'present']);

        $students = Student::query()
            ->where('section_id', $data['section_id'])
            ->whereIn('id', $statuses->keys())
            ->get();

        DB::transaction(function () use ($data, $students, $statuses): void {
            foreach ($students as $student) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $data['date'],
                        'period_number' => null,
                    ],
                    [
                        'school_id' => $student->school_id,
                        'branch_id' => $student->branch_id,
                        'academic_year_id' => $student->academic_year_id,
                        'class_id' => $student->class_id,
                        'section_id' => $student->section_id,
                        'status' => $statuses[$student->id],
                        'marked_by' => auth()->id(),
                        'marked_at' => now(),
                    ]
                );
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
}
