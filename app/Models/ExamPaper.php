<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ExamPaper extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'exam_schedule_id', 'class_id', 'section_id',
        'teacher_id', 'title', 'instructions', 'file_path',
    ];

    public function schedule()
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}