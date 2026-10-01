<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'max_days', 'is_paid', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_paid' => 'boolean', 'is_active' => 'boolean'];
    }
}
