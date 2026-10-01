<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'sort_order', 'group', 'has_sections', 'is_active',
    ];

    protected function casts(): array
    {
        return ['has_sections' => 'boolean', 'is_active' => 'boolean'];
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject')
            ->withPivot('teacher_id', 'periods_per_week', 'max_marks_theory', 'max_marks_practical')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
