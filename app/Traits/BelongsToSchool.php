<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $query): void {
            $user = Auth::user();

            if ($user?->user_type === 'super_admin') {
                return;
            }

            if (! $user || ! $user->school_id) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($query->getModel()->qualifyColumn('school_id'), $user->school_id);
        });

        static::creating(function (Model $model): void {
            static::enforceSchoolOwnership($model);
        });

        static::updating(function (Model $model): void {
            static::enforceSchoolOwnership($model);
        });
    }

    protected static function enforceSchoolOwnership(Model $model): void
    {
        $user = Auth::user();

        if (! $user || $user->user_type === 'super_admin') {
            return;
        }

        if (! $user->school_id) {
            throw new AuthorizationException('Your account is not assigned to a school.');
        }

        $model->setAttribute('school_id', $user->school_id);

        if ($user->branch_id && ($model->isFillable('branch_id') || array_key_exists('branch_id', $model->getAttributes()))) {
            $model->setAttribute('branch_id', $user->branch_id);

            return;
        }

        $branchId = $model->getAttribute('branch_id');

        if ($branchId && Branch::query()->whereKey($branchId)->doesntExist()) {
            throw new AuthorizationException('The selected branch is outside your school.');
        }
    }

    public function scopeWithoutSchoolScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }

    public function school()
    {
        return $this->belongsTo(\App\Models\School::class);
    }
}