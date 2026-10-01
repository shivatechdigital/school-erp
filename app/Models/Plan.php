<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'monthly_price', 'yearly_price', 'max_students',
        'max_branches', 'max_staff', 'storage_mb', 'features', 'is_popular', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'features' => 'array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
