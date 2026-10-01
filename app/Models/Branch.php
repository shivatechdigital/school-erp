<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id', 'name', 'code', 'is_main_branch', 'address', 'city', 'state',
        'pincode', 'phone', 'email', 'principal_name', 'principal_phone', 'status',
    ];

    protected function casts(): array
    {
        return ['is_main_branch' => 'boolean'];
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
