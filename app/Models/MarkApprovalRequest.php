<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class MarkApprovalRequest extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'exam_schedule_id', 'student_id', 'class_id', 'subject_id',
        'existing_mark_id', 'requested_by', 'assigned_teacher_id', 'theory_marks',
        'practical_marks', 'remark', 'status', 'response_note', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'theory_marks' => 'decimal:2',
            'practical_marks' => 'decimal:2',
            'responded_at' => 'datetime',
        ];
    }

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(User::class, 'assigned_teacher_id');
    }

    public function mark()
    {
        return $this->belongsTo(Mark::class, 'existing_mark_id');
    }
}