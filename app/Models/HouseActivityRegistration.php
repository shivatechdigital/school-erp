<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseActivityRegistration extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'house_activity_id', 'house_id', 'student_id', 'status',
        'applied_by', 'decided_by', 'decided_at', 'remarks',
    ];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(HouseActivity::class, 'house_activity_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
