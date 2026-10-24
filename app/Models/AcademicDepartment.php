<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicDepartment extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'academic_department_id');
    }
}
