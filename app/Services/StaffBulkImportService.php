<?php

namespace App\Services;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\Department;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;
use League\Csv\Bom;
use League\Csv\Reader;
use League\Csv\Writer;
use Throwable;

class StaffBulkImportService
{
    /** Internal field key => label prefix used to locate the matching column in the uploaded file. */
    private const COLUMNS = [
        'name' => 'Full Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'alternate_phone' => 'Alternate Phone',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'blood_group' => 'Blood Group',
        'user_type' => 'Role',
        'school_name' => 'School Name',
        'department' => 'Department',
        'designation' => 'Designation',
        'qualification' => 'Qualification',
        'joining_date' => 'Joining Date',
        'salary' => 'Salary',
        'status' => 'Status',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'pincode' => 'Pincode',
    ];

    private const HEADER_LABELS = [
        'Full Name (required)',
        'Email (required)',
        'Phone (required)',
        'Alternate Phone',
        'Gender (male/female/other)',
        'Date of Birth (DD-MM-YYYY)',
        'Blood Group (A+/A-/B+/B-/AB+/AB-/O+/O-)',
        'Role (required) (school_admin/branch_admin/teacher/accountant/librarian/transport_manager/receptionist/hr/doctor)',
        'School Name (required only when uploaded by Super Admin)',
        'Department (must match an existing department name, optional)',
        'Designation',
        'Qualification',
        'Joining Date (required) (DD-MM-YYYY)',
        'Salary',
        'Status (active/inactive/suspended, default active)',
        'Address',
        'City',
        'State',
        'Pincode',
    ];

    private const SAMPLE_EMAIL = 'example@sample.com';

    /** Generates the downloadable demo CSV template with instructions baked into the headers plus one sample row. */
    public function templateCsv(): string
    {
        $writer = Writer::createFromString('');
        $writer->setOutputBOM(Bom::Utf8);
        $writer->insertOne(self::HEADER_LABELS);
        $writer->insertOne([
            'Example Staff (delete this row before uploading)',
            self::SAMPLE_EMAIL,
            '9999999999',
            '',
            'male',
            '15-08-1990',
            'O+',
            'teacher',
            '',
            'Science',
            'TGT',
            'B.Ed',
            '01-06-2024',
            '35000',
            'active',
            '123 Example Street',
            'Delhi',
            'Delhi',
            '110001',
        ]);

        return $writer->toString();
    }

    /**
     * Imports staff rows from the uploaded CSV. Employee ID and password are always auto-assigned.
     *
     * @return array{created: int, skipped: int, errors: array<int, string>}
     */
    public function import(string $filePath, User $actingUser): array
    {
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setHeaderOffset(0);
        $reader->skipInputBOM();

        $columnMap = $this->mapColumns($reader->getHeader());

        $created = 0;
        $skipped = 0;
        $errors = [];
        $seenEmails = [];

        foreach ($reader->getRecords() as $offset => $record) {
            $rowNumber = $offset + 1; // offset is 0 at the header row, so this matches the row number seen in Excel
            $row = $this->extractRow($record, $columnMap);

            if (strcasecmp(trim((string) ($row['email'] ?? '')), self::SAMPLE_EMAIL) === 0) {
                continue; // sample/demo row, ignored silently
            }

            if (trim((string) ($row['name'] ?? '')) === '' && trim((string) ($row['email'] ?? '')) === '') {
                continue; // blank row
            }

            try {
                $this->importRow($row, $actingUser, $seenEmails);
                $created++;
            } catch (InvalidArgumentException $e) {
                $skipped++;
                $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
            }
        }

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
    }

    /** @return array<string, string> internal field key => actual header text found in the file */
    private function mapColumns(array $rawHeaders): array
    {
        $map = [];

        foreach ($rawHeaders as $header) {
            $clean = trim((string) $header);

            foreach (self::COLUMNS as $key => $prefix) {
                if (stripos($clean, $prefix) === 0) {
                    $map[$key] = $header;
                    break;
                }
            }
        }

        return $map;
    }

    /** @return array<string, string> */
    private function extractRow(array $record, array $columnMap): array
    {
        $row = [];

        foreach (self::COLUMNS as $key => $prefix) {
            $header = $columnMap[$key] ?? null;
            $row[$key] = $header !== null ? trim((string) ($record[$header] ?? '')) : '';
        }

        return $row;
    }

    /** @param array<string, bool> $seenEmails */
    private function importRow(array $row, User $actingUser, array &$seenEmails): void
    {
        $name = $row['name'];
        $email = strtolower($row['email']);
        $phone = $row['phone'];
        $role = strtolower($row['user_type']);

        if ($name === '') {
            throw new InvalidArgumentException('Full Name is required.');
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid Email is required.');
        }

        if (isset($seenEmails[$email])) {
            throw new InvalidArgumentException('Duplicate email within the uploaded file.');
        }

        if (User::withTrashed()->where('email', $email)->exists()) {
            throw new InvalidArgumentException('A user with this email already exists.');
        }

        if ($phone === '') {
            throw new InvalidArgumentException('Phone is required.');
        }

        if (! in_array($role, StaffResource::STAFF_TYPES, true)) {
            throw new InvalidArgumentException('Role must be one of: '.implode(', ', StaffResource::STAFF_TYPES).'.');
        }

        if ($role === 'school_admin' && ! in_array($actingUser->user_type, ['super_admin', 'school_admin'], true)) {
            throw new InvalidArgumentException('You are not allowed to assign the School Admin role.');
        }

        if ($row['joining_date'] === '') {
            throw new InvalidArgumentException('Joining Date is required.');
        }

        $joiningDate = $this->parseDate($row['joining_date']);
        if (! $joiningDate) {
            throw new InvalidArgumentException('Joining Date must be in DD-MM-YYYY format.');
        }

        $dateOfBirth = null;
        if ($row['date_of_birth'] !== '') {
            $dateOfBirth = $this->parseDate($row['date_of_birth']);
            if (! $dateOfBirth) {
                throw new InvalidArgumentException('Date of Birth must be in DD-MM-YYYY format.');
            }
        }

        $gender = strtolower($row['gender']) ?: null;
        if ($gender && ! in_array($gender, ['male', 'female', 'other'], true)) {
            throw new InvalidArgumentException('Gender must be male, female or other.');
        }

        $bloodGroup = strtoupper($row['blood_group']) ?: null;
        if ($bloodGroup && ! in_array($bloodGroup, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) {
            throw new InvalidArgumentException('Blood Group is invalid.');
        }

        $status = strtolower($row['status']) ?: 'active';
        if (! in_array($status, ['active', 'inactive', 'suspended'], true)) {
            throw new InvalidArgumentException('Status must be active, inactive or suspended.');
        }

        $salary = null;
        if ($row['salary'] !== '') {
            if (! is_numeric($row['salary'])) {
                throw new InvalidArgumentException('Salary must be numeric.');
            }
            $salary = (float) $row['salary'];
        }

        [$schoolId, $branchId] = $this->resolveSchoolAndBranch($row, $actingUser);

        $departmentId = null;
        if ($row['department'] !== '') {
            $department = Department::query()
                ->where('school_id', $schoolId)
                ->whereRaw('LOWER(name) = ?', [strtolower($row['department'])])
                ->first();

            if (! $department) {
                throw new InvalidArgumentException("Department '{$row['department']}' not found.");
            }

            $departmentId = $department->id;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'must_change_password' => true,
            'school_id' => $schoolId,
            'branch_id' => $branchId,
            'employee_code' => $this->nextEmployeeCode(),
            'phone' => $phone,
            'alternate_phone' => $row['alternate_phone'] ?: null,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'blood_group' => $bloodGroup,
            'address' => $row['address'] ?: null,
            'city' => $row['city'] ?: null,
            'state' => $row['state'] ?: null,
            'pincode' => $row['pincode'] ?: null,
            'qualification' => $row['qualification'] ?: null,
            'designation' => $row['designation'] ?: null,
            'department_id' => $departmentId,
            'joining_date' => $joiningDate,
            'salary' => $salary,
            'user_type' => $role,
            'status' => $status,
        ]);

        $seenEmails[$email] = true;
    }

    /** @return array{0: int|null, 1: int|null} */
    private function resolveSchoolAndBranch(array $row, User $actingUser): array
    {
        if ($actingUser->user_type !== 'super_admin') {
            return [$actingUser->school_id, $actingUser->branch_id];
        }

        $schoolName = trim($row['school_name']);
        if ($schoolName === '') {
            throw new InvalidArgumentException('School Name is required for Super Admin uploads.');
        }

        $school = School::query()->whereRaw('LOWER(name) = ?', [strtolower($schoolName)])->first();
        if (! $school) {
            throw new InvalidArgumentException("School '{$schoolName}' not found.");
        }

        return [$school->id, null];
    }

    private function nextEmployeeCode(): string
    {
        return 'EMP-'.date('Y').'-'.str_pad((string) (User::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    private function parseDate(string $value): ?Carbon
    {
        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }
}
