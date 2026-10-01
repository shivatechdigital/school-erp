<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\FeeAssignment;
use App\Models\FeeCollection;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Plan;
use App\Models\Route;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentTransport;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Timetable;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Seeds sample data for every module and then verifies observers, calculations
 * and tenant scoping. Fails loudly if any check does not pass.
 */
class DemoDataSeeder extends Seeder
{
    private School $school;

    private Branch $branch;

    private AcademicYear $year;

    private User $superAdmin;

    private User $schoolAdmin;

    /** @var array<int, User> */
    private array $teachers = [];

    /** @var array<string, Section> */
    private array $sections = [];

    /** @var array<int, Student> */
    private array $students = [];

    /** @var array<string, Subject> */
    private array $subjects = [];

    /** @var array<int, string> */
    private array $failures = [];

    public function run(): void
    {
        $this->superAdmin = User::query()->where('user_type', 'super_admin')->firstOrFail();

        // Tenant scopes hide everything when no one is logged in, so act as super admin.
        Auth::setUser($this->superAdmin);

        $this->school = School::query()->where('code', 'SCH001')->firstOrFail();
        $this->branch = Branch::query()->where('school_id', $this->school->id)->firstOrFail();
        $this->year = AcademicYear::query()->where('school_id', $this->school->id)->where('is_current', true)->firstOrFail();

        $this->seedSubscription();
        $this->seedStaff();
        $this->seedHr();
        $this->seedAcademics();
        $this->seedStudents();
        $this->seedAttendance();
        $this->seedFees();
        $this->seedExams();
        $this->seedTimetable();
        $this->seedNotices();
        $this->seedHomework();
        $this->seedTransport();
        $this->seedHostel();
        $this->seedLibrary();

        $this->verify();

        Auth::forgetUser();

        if ($this->failures !== []) {
            throw new RuntimeException("Demo data verification failed:\n - ".implode("\n - ", $this->failures));
        }

        $this->command?->info('✅ Demo data seeded and all checks passed.');
        $this->command?->line('   Login: admin@schoolorp.com / password (super admin)');
        $this->command?->line('   Login: principal@demo.com / password (school admin)');
    }

    private function base(): array
    {
        return ['school_id' => $this->school->id, 'branch_id' => $this->branch->id];
    }

    private function seedSubscription(): void
    {
        Subscription::create([
            'school_id' => $this->school->id,
            'plan_id' => Plan::query()->where('slug', 'growth')->value('id'),
            'billing_cycle' => 'yearly',
            'amount' => 25000,
            'status' => 'active',
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->endOfYear(),
        ]);
    }

    private function seedStaff(): void
    {
        $staff = fn (string $name, string $email, string $type, array $extra = []) => User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'school_id' => $this->school->id,
            'branch_id' => $type === 'school_admin' ? null : $this->branch->id,
            'user_type' => $type,
            'status' => 'active',
            'phone' => '98'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'joining_date' => '2024-04-01',
        ] + $extra);

        $this->schoolAdmin = $staff('Principal Sharma', 'principal@demo.com', 'school_admin');

        foreach (['Anita Verma' => 'Mathematics', 'Rahul Gupta' => 'Science', 'Priya Singh' => 'English'] as $name => $subject) {
            $this->teachers[] = $staff($name, strtolower(str_replace(' ', '.', $name)).'@demo.com', 'teacher', [
                'qualification' => 'M.Sc, B.Ed',
                'designation' => "{$subject} Teacher",
            ]);
        }

        $staff('Library Incharge', 'librarian@demo.com', 'librarian');
        $staff('Transport Manager', 'transport@demo.com', 'transport_manager');
        $staff('Accounts Officer', 'accounts@demo.com', 'accountant');
    }

    private function seedHr(): void
    {
        Department::create(['school_id' => $this->school->id, 'name' => 'Science', 'code' => 'SCI']);
        Department::create(['school_id' => $this->school->id, 'name' => 'Administration', 'code' => 'ADM']);

        Designation::create(['school_id' => $this->school->id, 'name' => 'PGT', 'code' => 'PGT', 'category' => 'teaching', 'sort_order' => 1]);
        Designation::create(['school_id' => $this->school->id, 'name' => 'Clerk', 'code' => 'CLK', 'category' => 'non_teaching', 'sort_order' => 2]);

        $casual = LeaveType::create(['school_id' => $this->school->id, 'name' => 'Casual Leave', 'code' => 'CL', 'max_days' => 12]);
        LeaveType::create(['school_id' => $this->school->id, 'name' => 'Sick Leave', 'code' => 'SL', 'max_days' => 10]);

        Leave::create([
            'school_id' => $this->school->id,
            'user_id' => $this->teachers[0]->id,
            'leave_type_id' => $casual->id,
            'from_date' => now()->addDays(3)->toDateString(),
            'to_date' => now()->addDays(4)->toDateString(),
            'total_days' => 2,
            'reason' => 'Family function',
            'status' => 'pending',
        ]);
    }

    private function seedAcademics(): void
    {
        foreach (['Mathematics' => 'MATH', 'Science' => 'SCI', 'English' => 'ENG', 'Hindi' => 'HIN', 'Computer' => 'COMP'] as $name => $code) {
            $this->subjects[$code] = Subject::create([
                'school_id' => $this->school->id,
                'name' => $name,
                'code' => $code,
                'type' => $code === 'COMP' ? 'both' : 'theory',
            ]);
        }

        $classes = SchoolClass::query()->where('school_id', $this->school->id)->whereIn('name', ['Class 1', 'Class 2'])->get();

        foreach ($classes as $class) {
            $class->subjects()->attach([
                $this->subjects['MATH']->id => ['teacher_id' => $this->teachers[0]->id],
                $this->subjects['SCI']->id => ['teacher_id' => $this->teachers[1]->id],
                $this->subjects['ENG']->id => ['teacher_id' => $this->teachers[2]->id],
            ]);

            foreach (['A', 'B'] as $i => $name) {
                $this->sections["{$class->name}-{$name}"] = Section::create($this->base() + [
                    'class_id' => $class->id,
                    'academic_year_id' => $this->year->id,
                    'name' => $name,
                    'capacity' => 40,
                    'room_no' => ($class->sort_order * 100 + $i + 1),
                    'class_teacher_id' => $this->teachers[$i]->id,
                ]);
            }
        }
    }

    private function seedStudents(): void
    {
        $names = [
            ['Aarav', 'Mehta', 'male'], ['Diya', 'Patel', 'female'], ['Vivaan', 'Reddy', 'male'],
            ['Ananya', 'Iyer', 'female'], ['Kabir', 'Khan', 'male'], ['Ishita', 'Joshi', 'female'],
            ['Arjun', 'Nair', 'male'], ['Saanvi', 'Das', 'female'],
        ];

        $sectionKeys = array_keys($this->sections);

        foreach ($names as $i => [$first, $last, $gender]) {
            $section = $this->sections[$sectionKeys[$i % count($sectionKeys)]];

            $student = Student::create($this->base() + [
                'academic_year_id' => $this->year->id,
                'class_id' => $section->class_id,
                'section_id' => $section->id,
                'admission_no' => 'ADM-2025-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'roll_no' => (string) ($i + 1),
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $gender,
                'date_of_birth' => now()->subYears(7)->subDays($i * 30)->toDateString(),
                'admission_date' => '2025-04-01',
                'category' => 'General',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'status' => 'active',
            ]);

            $guardian = Guardian::create([
                'school_id' => $this->school->id,
                'name' => "Mr. {$last}",
                'relation' => 'father',
                'gender' => 'male',
                'phone' => '97'.str_pad((string) ($i + 1), 8, '0', STR_PAD_LEFT),
                'occupation' => 'Business',
                'is_primary_contact' => true,
            ]);

            $student->guardians()->attach($guardian->id, ['relation' => 'father', 'is_primary' => true]);

            $this->students[] = $student;
        }
    }

    private function seedAttendance(): void
    {
        foreach ($this->students as $i => $student) {
            StudentAttendance::create($this->base() + [
                'academic_year_id' => $this->year->id,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'student_id' => $student->id,
                'date' => today()->toDateString(),
                'status' => $i === 0 ? 'absent' : 'present',
                'marked_by' => $this->teachers[0]->id,
                'marked_at' => now(),
            ]);
        }

        foreach ($this->teachers as $teacher) {
            StaffAttendance::create($this->base() + [
                'user_id' => $teacher->id,
                'date' => today()->toDateString(),
                'status' => 'present',
                'check_in' => '08:00:00',
                'check_out' => '14:30:00',
                'total_hours' => 6.5,
            ]);
        }
    }

    private function seedFees(): void
    {
        $tuition = FeeHead::create(['school_id' => $this->school->id, 'name' => 'Tuition Fee', 'code' => 'TF', 'frequency' => 'monthly']);
        FeeHead::create(['school_id' => $this->school->id, 'name' => 'Admission Fee', 'code' => 'AF', 'frequency' => 'one_time']);

        $student = $this->students[0];

        $structure = FeeStructure::create($this->base() + [
            'academic_year_id' => $this->year->id,
            'class_id' => $student->class_id,
            'fee_head_id' => $tuition->id,
            'category' => 'All',
            'amount' => 2500,
            'fine_per_day' => 10,
            'due_date' => now()->startOfMonth()->addDays(9)->toDateString(),
        ]);

        $assignment = FeeAssignment::create([
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'assigned_amount' => 2500,
            'discount_amount' => 0,
            'net_amount' => 2500,
            'due_date' => $structure->due_date,
            'status' => 'paid',
        ]);

        // total_amount is deliberately wrong here: FeeCollectionObserver must recalculate it.
        FeeCollection::create([
            'school_id' => $this->school->id,
            'branch_id' => $this->branch->id,
            'student_id' => $student->id,
            'fee_assignment_id' => $assignment->id,
            'receipt_no' => FeeCollection::generateReceiptNo(),
            'amount' => 2500,
            'fine_amount' => 50,
            'total_amount' => 0,
            'payment_mode' => 'upi',
            'transaction_id' => 'UPI123456',
            'payment_date' => today()->toDateString(),
            'collected_by' => $this->superAdmin->id,
        ]);
    }

    private function seedExams(): void
    {
        $exam = Exam::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->year->id,
            'name' => 'Unit Test 1',
            'code' => 'UT1',
            'type' => 'unit_test',
            'total_marks' => 50,
            'pass_marks' => 17,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'status' => 'completed',
        ]);

        $student = $this->students[0];

        $schedule = ExamSchedule::create([
            'school_id' => $this->school->id,
            'exam_id' => $exam->id,
            'class_id' => $student->class_id,
            'subject_id' => $this->subjects['MATH']->id,
            'exam_date' => now()->subDays(8)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'max_marks' => 50,
            'pass_marks' => 17,
        ]);

        foreach ($this->students as $i => $s) {
            if ($s->class_id !== $student->class_id) {
                continue;
            }

            Mark::create([
                'school_id' => $this->school->id,
                'exam_id' => $exam->id,
                'exam_schedule_id' => $schedule->id,
                'student_id' => $s->id,
                'class_id' => $s->class_id,
                'subject_id' => $this->subjects['MATH']->id,
                'theory_marks' => $i === 0 ? 46 : 12,
                'max_marks' => 50,
                'pass_marks' => 17,
            ]);
        }
    }

    private function seedTimetable(): void
    {
        $section = $this->sections['Class 1-A'];
        $slots = [
            [1, '08:00', '08:40', 'MATH', 0, false],
            [2, '08:40', '09:20', 'SCI', 1, false],
            [3, '09:20', '09:40', null, null, true],
            [4, '09:40', '10:20', 'ENG', 2, false],
        ];

        foreach ($slots as [$period, $start, $end, $subject, $teacher, $isBreak]) {
            Timetable::create($this->base() + [
                'academic_year_id' => $this->year->id,
                'class_id' => $section->class_id,
                'section_id' => $section->id,
                'subject_id' => $subject ? $this->subjects[$subject]->id : null,
                'teacher_id' => $teacher !== null ? $this->teachers[$teacher]->id : null,
                'day_of_week' => 'monday',
                'period_number' => $period,
                'start_time' => $start,
                'end_time' => $end,
                'room_no' => $isBreak ? null : 'Room 101',
                'is_break' => $isBreak,
                'break_label' => $isBreak ? 'Lunch Break' : null,
            ]);
        }
    }

    private function seedNotices(): void
    {
        $notices = [
            ['Annual Day Celebration', 'all', 'normal', null],
            ['Staff Meeting on Saturday', 'staff', 'high', null],
            ['Class 1 Picnic Consent Form', 'specific_class', 'urgent', $this->sections['Class 1-A']],
        ];

        foreach ($notices as [$title, $audience, $priority, $section]) {
            Notice::create($this->base() + [
                'published_by' => $this->schoolAdmin->id,
                'title' => $title,
                'content' => "<p>{$title} - details will be shared by the class teacher.</p>",
                'priority' => $priority,
                'target_audience' => $audience,
                'class_id' => $section?->class_id,
                'section_id' => $section?->id,
                'publish_date' => today()->toDateString(),
                'expiry_date' => today()->addDays(15)->toDateString(),
            ]);
        }
    }

    private function seedHomework(): void
    {
        $section = $this->sections['Class 1-A'];

        $homework = Homework::create($this->base() + [
            'academic_year_id' => $this->year->id,
            'class_id' => $section->class_id,
            'section_id' => $section->id,
            'subject_id' => $this->subjects['MATH']->id,
            'teacher_id' => $this->teachers[0]->id,
            'title' => 'Addition Worksheet 1',
            'description' => '<p>Solve all 20 sums.</p>',
            'assigned_date' => today()->subDays(2)->toDateString(),
            'due_date' => today()->addDay()->toDateString(),
            'max_marks' => 20,
        ]);

        foreach ($this->students as $student) {
            if ($student->section_id !== $section->id) {
                continue;
            }

            HomeworkSubmission::create($this->base() + [
                'homework_id' => $homework->id,
                'student_id' => $student->id,
                'submitted_at' => now()->subDay(),
                'student_notes' => 'Done',
                'status' => 'evaluated',
                'marks_obtained' => 18,
                'teacher_feedback' => 'Good work',
                'evaluated_by' => $this->teachers[0]->id,
                'evaluated_at' => now(),
            ]);
        }
    }

    private function seedTransport(): void
    {
        $vehicle = Vehicle::create($this->base() + [
            'vehicle_number' => 'DL-01-AB-1234',
            'vehicle_model' => 'Tata Starbus 40 Seater',
            'capacity' => 40,
            'driver_name' => 'Ramesh Kumar',
            'driver_phone' => '9811111111',
            'driver_license' => 'DL-0420110012345',
        ]);

        $route = Route::create($this->base() + [
            'vehicle_id' => $vehicle->id,
            'title' => 'Route 1 - Sector 62 to School',
            'start_point' => 'Sector 62',
            'end_point' => 'School Campus',
        ]);

        $stops = [];
        foreach ([['Sector 62 Metro', '07:00', '14:40', 1500], ['City Mall', '07:15', '14:25', 1200]] as $i => [$name, $pickup, $drop, $fare]) {
            $stops[] = $route->stops()->create($this->base() + [
                'stop_name' => $name,
                'pickup_time' => $pickup,
                'drop_time' => $drop,
                'monthly_fare' => $fare,
                'stop_order' => $i + 1,
            ]);
        }

        StudentTransport::create($this->base() + [
            'student_id' => $this->students[1]->id,
            'route_id' => $route->id,
            'route_stop_id' => $stops[0]->id,
            'trip_type' => 'both',
            'start_date' => '2025-04-01',
        ]);
    }

    private function seedHostel(): void
    {
        $hostel = Hostel::create($this->base() + [
            'name' => 'Block A - Boys Hostel',
            'type' => 'boys',
            'warden_name' => 'Mr. Yadav',
            'warden_phone' => '9822222222',
        ]);

        $room = HostelRoom::create($this->base() + [
            'hostel_id' => $hostel->id,
            'room_number' => '101',
            'room_type' => 'double',
            'bed_capacity' => 2,
            'cost_per_bed' => 3000,
        ]);

        HostelAllocation::create($this->base() + [
            'student_id' => $this->students[2]->id,
            'hostel_room_id' => $room->id,
            'bed_number' => 'Bed 1',
            'allocated_date' => '2025-04-01',
            'status' => 'allocated',
        ]);
    }

    private function seedLibrary(): void
    {
        $category = BookCategory::create($this->base() + ['name' => 'Story Books']);

        foreach ([['Panchatantra Tales', 'Vishnu Sharma', 3], ['Malgudi Days', 'R. K. Narayan', 2]] as [$title, $author, $copies]) {
            Book::create($this->base() + [
                'book_category_id' => $category->id,
                'title' => $title,
                'author' => $author,
                'isbn_no' => '978-81-'.random_int(100000, 999999),
                'rack_no' => 'Rack A-1',
                'total_copies' => $copies,
                'available_copies' => $copies,
                'price' => 250,
            ]);
        }
    }

    private function verify(): void
    {
        $this->command?->info('Running checks...');

        $this->check('All modules have data', function (): bool {
            foreach ([
                Student::class, Guardian::class, Section::class, Subject::class, StudentAttendance::class,
                StaffAttendance::class, FeeCollection::class, Exam::class, Mark::class, Timetable::class,
                Notice::class, Homework::class, HomeworkSubmission::class, Vehicle::class, Route::class,
                StudentTransport::class, Hostel::class, HostelRoom::class, HostelAllocation::class,
                Book::class, BookCategory::class, Leave::class, Subscription::class,
            ] as $model) {
                if ($model::query()->count() === 0) {
                    throw new RuntimeException("{$model} has no rows");
                }
            }

            return true;
        });

        $this->check('FeeCollection total = amount + fine (observer)', fn (): bool => (float) FeeCollection::query()->first()->total_amount === 2550.0);

        $this->check('Mark grade/result auto-calculated (observer)', function (): bool {
            $marks = Mark::query()->orderBy('id')->get();

            return $marks->first()->grade === 'A+' && $marks->first()->result === 'pass'
                && $marks->last()->grade === 'F' && $marks->last()->result === 'fail';
        });

        $this->check('Timetable clash detection', function (): bool {
            $teacherId = $this->teachers[0]->id;
            $clash = Timetable::query()->overlapping('monday', '08:20', '09:00')->where('teacher_id', $teacherId)->exists();
            $backToBack = Timetable::query()->overlapping('monday', '08:40', '09:20')->where('teacher_id', $teacherId)->exists();

            return $clash && ! $backToBack;
        });

        $this->check('Library stock decrements on issue and restores on return (observer)', function (): bool {
            $book = Book::query()->where('title', 'Panchatantra Tales')->firstOrFail();

            $issue = BookIssue::create($this->base() + [
                'book_id' => $book->id,
                'member_type' => 'student',
                'student_id' => $this->students[3]->id,
                'issue_date' => today()->subDays(20)->toDateString(),
                'due_date' => today()->subDays(6)->toDateString(),
            ]);
            $afterIssue = $book->fresh()->available_copies;

            $issue->update(['status' => 'returned', 'return_date' => today()->toDateString(), 'fine_amount' => 30, 'fine_status' => 'paid']);
            $afterReturn = $book->fresh()->available_copies;

            // A still-issued record for the UI.
            BookIssue::create($this->base() + [
                'book_id' => $book->id,
                'member_type' => 'staff',
                'user_id' => $this->teachers[1]->id,
                'issue_date' => today()->toDateString(),
                'due_date' => today()->addDays(14)->toDateString(),
            ]);

            return $afterIssue === 2 && $afterReturn === 3 && $book->fresh()->available_copies === 2
                && $issue->fresh()->issued_by === $this->superAdmin->id;
        });

        $this->check('Hostel available beds', fn (): bool => HostelRoom::query()->first()->available_beds === 1);

        $this->check('Route stops ordered + transport fare linked', fn (): bool => Route::query()->first()->stops->pluck('stop_order')->all() === [1, 2]
            && (float) StudentTransport::query()->first()->stop->monthly_fare === 1500.0);

        $this->check('Tenant isolation: school admin cannot see another school', function (): bool {
            $other = School::create([
                'name' => 'Other School', 'code' => 'SCH002', 'email' => 'other@school.com', 'phone' => '9000000000',
                'address' => 'Somewhere', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400001', 'status' => 'active',
            ]);
            $otherBranch = Branch::create(['school_id' => $other->id, 'name' => 'Main', 'code' => 'BR002', 'is_main_branch' => true]);
            $otherYear = AcademicYear::create(['school_id' => $other->id, 'name' => '2025-2026', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'is_current' => true]);
            $otherClass = SchoolClass::create(['school_id' => $other->id, 'name' => 'Class 1', 'sort_order' => 1]);
            Student::create([
                'school_id' => $other->id, 'branch_id' => $otherBranch->id, 'academic_year_id' => $otherYear->id,
                'class_id' => $otherClass->id, 'admission_no' => 'OTH-001', 'first_name' => 'Other', 'gender' => 'male',
                'date_of_birth' => '2018-01-01', 'admission_date' => '2025-04-01',
            ]);
            Notice::create([
                'school_id' => $other->id, 'published_by' => $this->superAdmin->id, 'title' => 'Other school notice',
                'content' => 'x', 'publish_date' => today()->toDateString(),
            ]);

            Auth::setUser($this->schoolAdmin);
            $visibleStudents = Student::query()->count();
            $visibleNotices = Notice::query()->count();
            $visibleTeachers = User::query()->sameSchool()->where('user_type', 'teacher')->count();
            Auth::setUser($this->superAdmin);

            return $visibleStudents === count($this->students)
                && $visibleNotices === 3
                && $visibleTeachers === count($this->teachers)
                && Student::query()->count() === count($this->students) + 1;
        });
    }

    private function check(string $label, callable $assertion): void
    {
        try {
            $passed = (bool) $assertion();
            $error = $passed ? null : 'assertion returned false';
        } catch (\Throwable $e) {
            $passed = false;
            $error = $e->getMessage();
        }

        if ($passed) {
            $this->command?->line("  ✅ {$label}");

            return;
        }

        $this->command?->error("  ❌ {$label}: {$error}");
        $this->failures[] = "{$label}: {$error}";
    }
}
