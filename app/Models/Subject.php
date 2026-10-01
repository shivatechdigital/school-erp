<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'code', 'type', 'is_optional', 'is_active'];

    protected function casts(): array
    {
        return ['is_optional' => 'boolean', 'is_active' => 'boolean'];
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject')
            ->withPivot('teacher_id', 'periods_per_week', 'max_marks_theory', 'max_marks_practical')
            ->withTimestamps();
    }
}
