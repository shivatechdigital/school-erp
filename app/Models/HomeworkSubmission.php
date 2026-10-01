<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'homework_id',
        'student_id',
        'submitted_at',
        'submitted_files',
        'student_notes',
        'status',
        'marks_obtained',
        'teacher_feedback',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'evaluated_at' => 'datetime',
        'submitted_files' => 'array',
        'marks_obtained' => 'decimal:2',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
