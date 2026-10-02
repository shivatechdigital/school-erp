<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkAudit extends Model
{
    protected $fillable = ['mark_id', 'changed_by', 'old_values', 'new_values', 'changed_at'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    public function mark()
    {
        return $this->belongsTo(Mark::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}