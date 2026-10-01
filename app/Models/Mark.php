<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'exam_id', 'exam_schedule_id',
        'student_id', 'class_id', 'subject_id',
        'theory_marks', 'practical_marks', 'total_marks',
        'max_marks', 'pass_marks', 'percentage', 'grade',
        'result', 'rank_in_class', 'remark',
        'entered_by', 'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'theory_marks' => 'decimal:2',
            'practical_marks' => 'decimal:2',
            'total_marks' => 'decimal:2',
            'max_marks' => 'decimal:2',
            'pass_marks' => 'decimal:2',
            'percentage' => 'decimal:2',
            'entered_at' => 'datetime',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public static function gradeFor(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 33 => 'D',
            default => 'F',
        };
    }

    public function calculateResult(): void
    {
        $total = (float) ($this->theory_marks ?? 0) + (float) ($this->practical_marks ?? 0);
        $this->total_marks = $total;
        $this->percentage = $this->max_marks > 0
            ? round(($total / $this->max_marks) * 100, 2)
            : 0;

        $this->grade = static::gradeFor((float) $this->percentage);

        // Manually chosen absent/withheld results must not be overwritten.
        if (! in_array($this->result, ['absent', 'withheld'], true)) {
            $this->result = $total >= $this->pass_marks ? 'pass' : 'fail';
        }
    }
}
