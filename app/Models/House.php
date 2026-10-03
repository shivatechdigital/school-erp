<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'name', 'color', 'house_master_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function houseMaster()
    {
        return $this->belongsTo(User::class, 'house_master_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
