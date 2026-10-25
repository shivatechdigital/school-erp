<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseActivity extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'created_by', 'name', 'category', 'description', 'status',
        'max_participants_per_house', 'registration_start_date', 'registration_end_date',
    ];

    protected function casts(): array
    {
        return [
            'max_participants_per_house' => 'integer',
            'registration_start_date' => 'date',
            'registration_end_date' => 'date',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HouseActivityAssignment::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(HouseActivityRegistration::class);
    }

    public function approvedCountForHouse(int $houseId): int
    {
        return $this->registrations()
            ->where('house_id', $houseId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Creates a House Activity Assignment row for every active house of this activity's
     * school/branch that doesn't already have one. Safe to call repeatedly (idempotent).
     */
    public function syncHouseAssignments(): int
    {
        $alreadyAssignedHouseIds = $this->assignments()->pluck('house_id');

        $houses = House::query()
            ->withoutGlobalScope('branch')
            ->where('school_id', $this->school_id)
            ->where('is_active', true)
            ->when($this->branch_id, fn ($query) => $query->where(function ($query): void {
                $query->whereNull('branch_id')->orWhere('branch_id', $this->branch_id);
            }))
            ->whereNotIn('id', $alreadyAssignedHouseIds)
            ->get();

        foreach ($houses as $house) {
            $this->assignments()->create(['house_id' => $house->id]);
        }

        return $houses->count();
    }
}
