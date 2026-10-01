<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'academic_year_id', 'class_id',
        'fee_head_id', 'category', 'amount', 'fine_per_day',
        'due_date', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fine_per_day' => 'decimal:2',
            'due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function assignments()
    {
        return $this->hasMany(FeeAssignment::class);
    }
}
