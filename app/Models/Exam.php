<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'academic_year_id', 'name', 'code', 'type',
        'total_marks', 'pass_marks', 'weightage',
        'start_date', 'end_date', 'status', 'result_published',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'weightage' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'result_published' => 'boolean',
        ];
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}
