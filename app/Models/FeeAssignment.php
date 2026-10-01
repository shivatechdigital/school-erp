<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class FeeAssignment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'student_id', 'fee_structure_id',
        'assigned_amount', 'discount_amount', 'net_amount',
        'discount_reason', 'due_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function collections()
    {
        return $this->hasMany(FeeCollection::class);
    }

    public function totalPaid(): float
    {
        return (float) $this->collections()
            ->where('status', 'success')
            ->sum('total_amount');
    }

    public function balance(): float
    {
        return (float) $this->net_amount - $this->totalPaid();
    }
}
