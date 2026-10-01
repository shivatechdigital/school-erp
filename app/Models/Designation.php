<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'category', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
