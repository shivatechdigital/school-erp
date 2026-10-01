<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'academic_year_id',
        'class_id', 'section_id', 'student_id',
        'date', 'status', 'period_number',
        'remark', 'marked_by', 'marked_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'marked_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
