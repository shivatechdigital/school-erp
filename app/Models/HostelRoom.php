<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostelRoom extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'hostel_id',
        'room_number',
        'room_type',
        'bed_capacity',
        'cost_per_bed',
        'description',
        'is_active',
    ];

    protected $casts = [
        'bed_capacity' => 'integer',
        'cost_per_bed' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function activeAllocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class)->where('status', 'allocated');
    }

    public function getAvailableBedsAttribute(): int
    {
        $occupied = $this->relationLoaded('activeAllocations')
            ? $this->activeAllocations->count()
            : $this->activeAllocations()->count();

        return max(0, $this->bed_capacity - $occupied);
    }
}
