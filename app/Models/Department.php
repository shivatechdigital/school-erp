<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'name', 'code', 'description', 'is_active', 'head_user_id', 'sub_head_user_id',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    public function subHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sub_head_user_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(DepartmentRequest::class);
    }

    public function isHeadedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->head_user_id === $userId || $this->sub_head_user_id === $userId;
    }
}
