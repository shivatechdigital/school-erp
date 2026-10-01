<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'exam_id', 'class_id', 'subject_id',
        'exam_date', 'start_time', 'end_time',
        'max_marks', 'pass_marks', 'room_no', 'instructions',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'max_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
