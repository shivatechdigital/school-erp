<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'vehicle_number',
        'vehicle_model',
        'capacity',
        'driver_id',
        'driver_name',
        'driver_phone',
        'driver_license',
        'helper_name',
        'helper_phone',
        'gps_device_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
