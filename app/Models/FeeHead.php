<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class FeeHead extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'type', 'frequency', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}
