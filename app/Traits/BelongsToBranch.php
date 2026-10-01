<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch', function (Builder $query): void {
            $user = Auth::user();

            if (! $user) {
                $query->whereRaw('1 = 0');

                return;
            }

            if ($user->user_type !== 'super_admin' && $user->branch_id) {
                $query->where($query->getModel()->qualifyColumn('branch_id'), $user->branch_id);
            }
        });
    }

    public function scopeWithoutBranchScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('branch');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}