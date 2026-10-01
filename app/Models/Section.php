<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'class_id', 'academic_year_id', 'name', 'capacity',
        'current_strength', 'room_no', 'class_teacher_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
