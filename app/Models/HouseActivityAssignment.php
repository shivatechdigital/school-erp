<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseActivityAssignment extends Model
{
    protected $fillable = [
        'house_activity_id', 'house_id', 'incharge_user_id', 'incharge_label', 'captain_student_id',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(HouseActivity::class, 'house_activity_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function incharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incharge_user_id');
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'captain_student_id');
    }

    public function isInchargeUser(?int $userId): bool
    {
        return $userId !== null && $this->incharge_user_id === $userId;
    }
}
