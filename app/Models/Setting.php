<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'branch_id', 'group', 'key', 'value', 'type'];

    protected static function booted(): void
    {
        static::addGlobalScope('branch', function (Builder $query): void {
            $user = Auth::user();

            if (! $user) {
                $query->whereRaw('1 = 0');

                return;
            }

            if ($user->user_type !== 'super_admin' && $user->branch_id) {
                $query->where(function (Builder $settings) use ($user): void {
                    $settings->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
                });
            }
        });

        static::creating(function (Setting $setting): void {
            $user = Auth::user();

            if ($user && $user->user_type !== 'super_admin') {
                $setting->school_id = $user->school_id;

                if ($user->branch_id) {
                    $setting->branch_id = $user->branch_id;
                }
            }
        });

        static::updating(function (Setting $setting): void {
            $user = Auth::user();

            if ($user && $user->user_type !== 'super_admin') {
                $setting->school_id = $user->school_id;

                if ($user->branch_id) {
                    $setting->branch_id = $user->branch_id;
                }
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
