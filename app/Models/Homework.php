<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $table = 'homework';

    protected $fillable = [
        'school_id',
        'branch_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'max_marks',
        'attachments',
        'allow_late_submission',
        'is_active',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'attachments' => 'array',
        'allow_late_submission' => 'boolean',
        'is_active' => 'boolean',
        'max_marks' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'homework_sections')->withTimestamps();
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
