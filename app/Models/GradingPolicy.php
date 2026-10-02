<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class GradingPolicy extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'overall_pass_percentage', 'subject_pass_percentage', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'overall_pass_percentage' => 'decimal:2',
            'subject_pass_percentage' => 'decimal:2',
        ];
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}