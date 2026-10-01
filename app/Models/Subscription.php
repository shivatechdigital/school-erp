<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'plan_id', 'billing_cycle', 'amount', 'status', 'razorpay_subscription_id',
        'trial_ends_at', 'starts_at', 'ends_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
