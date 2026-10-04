<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\School;
use App\Models\User;
use App\Services\StaffBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use League\Csv\Writer;
use Tests\TestCase;

class StaffBulkImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'Full Name (required)', 'Email (required)', 'Phone (required)', 'Alternate Phone',
        'Gender (male/female/other)', 'Date of Birth (DD-MM-YYYY)', 'Blood Group (A+/A-/B+/B-/AB+/AB-/O+/O-)',
        'Role (required) (school_admin/branch_admin/teacher/accountant/librarian/transport_manager/receptionist/hr/doctor)',
        'School Name (required only when uploaded by Super Admin)',
        'Department (must match an existing department name, optional)',
        'Designation', 'Qualification', 'Joining Date (required) (DD-MM-YYYY)', 'Salary',
        'Status (active/inactive/suspended, default active)', 'Address', 'City', 'State', 'Pincode',
    ];

    private function school(): School
    {
        return School::query()->create([
            'name' => 'Demo School', 'code' => 'SCH001', 'email' => 'school@demo.com',
            'phone' => '9000000000', 'address' => 'Addr', 'city' => 'City', 'state' => 'State', 'pincode' => '110001',
        ]);
    }

    private function writeCsv(string $path, array $rows): void
    {
        $writer = Writer::createFromPath($path, 'w+');
        $writer->insertOne(self::HEADERS);
        $writer->insertAll($rows);
    }

    public function test_template_contains_expected_headers_and_sample_row(): void
    {
        $csv = (new StaffBulkImportService())->templateCsv();

        $this->assertStringContainsString('Full Name (required)', $csv);
        $this->assertStringContainsString('example@sample.com', $csv);
        $this->assertStringNotContainsString('Employee ID', $csv);
        $this->assertStringNotContainsString('Password', $csv);
    }

    public function test_import_creates_staff_with_auto_employee_code_and_default_password(): void
    {
        $school = $this->school();
        $department = Department::query()->create(['school_id' => $school->id, 'name' => 'Science']);
        $admin = User::factory()->create(['school_id' => $school->id, 'user_type' => 'school_admin', 'employee_code' => 'EMP-2026-0001']);
        $this->actingAs($admin);

        $path = sys_get_temp_dir().'/staff_import_test_'.uniqid().'.csv';
        $this->writeCsv($path, [
            ['John Teacher', 'john@demo.com', '9999999991', '', 'male', '15-08-1990', 'O+', 'teacher', '', 'Science', 'TGT', 'B.Ed', '01-06-2024', '35000', 'active', 'Addr', 'City', 'State', '110001'],
            ['Jane Accountant', 'jane@demo.com', '9999999992', '', 'female', '', '', 'accountant', '', '', '', '', '02-06-2024', '', '', '', '', '', ''],
        ]);

        $result = (new StaffBulkImportService())->import($path, $admin);
        unlink($path);

        $this->assertSame(2, $result['created']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame([], $result['errors']);

        $john = User::query()->where('email', 'john@demo.com')->firstOrFail();
        $this->assertSame($school->id, $john->school_id);
        $this->assertSame($department->id, $john->department_id);
        $this->assertTrue($john->must_change_password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $john->password));
        $this->assertNotNull($john->employee_code);
        $this->assertNotSame($admin->employee_code, $john->employee_code);

        $jane = User::query()->where('email', 'jane@demo.com')->firstOrFail();
        $this->assertSame('active', $jane->status);
        $this->assertNotSame($john->employee_code, $jane->employee_code);
    }

    public function test_import_skips_sample_row_and_reports_errors(): void
    {
        $school = $this->school();
        $admin = User::factory()->create(['school_id' => $school->id, 'user_type' => 'school_admin']);
        $this->actingAs($admin);

        $path = sys_get_temp_dir().'/staff_import_test_'.uniqid().'.csv';
        $this->writeCsv($path, [
            ['Example Staff (delete this row before uploading)', 'example@sample.com', '9999999999', '', 'male', '15-08-1990', 'O+', 'teacher', '', 'Science', 'TGT', 'B.Ed', '01-06-2024', '35000', 'active', 'Addr', 'City', 'State', '110001'],
            ['Bad Row', 'not-an-email', '9999999993', '', '', '', '', 'invalid_role', '', '', '', '', '03-06-2024', '', '', '', '', '', ''],
        ]);

        $result = (new StaffBulkImportService())->import($path, $admin);
        unlink($path);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Row 3', $result['errors'][0]);
        $this->assertFalse(User::query()->where('email', 'example@sample.com')->exists());
    }

    public function test_super_admin_import_requires_and_resolves_school_name(): void
    {
        $school = $this->school();
        $superAdmin = User::factory()->create(['user_type' => 'super_admin']);
        $this->actingAs($superAdmin);

        $path = sys_get_temp_dir().'/staff_import_test_'.uniqid().'.csv';
        $this->writeCsv($path, [
            ['Remote Teacher', 'remote@demo.com', '9999999994', '', '', '', '', 'teacher', 'Demo School', '', '', '', '04-06-2024', '', '', '', '', '', ''],
        ]);

        $result = (new StaffBulkImportService())->import($path, $superAdmin);
        unlink($path);

        $this->assertSame(1, $result['created']);
        $remote = User::query()->where('email', 'remote@demo.com')->firstOrFail();
        $this->assertSame($school->id, $remote->school_id);
        $this->assertNull($remote->branch_id);
    }
}
