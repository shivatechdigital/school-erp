<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'start_date', 'end_date', 'is_current', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
