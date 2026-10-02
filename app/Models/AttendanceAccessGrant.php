<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AttendanceAccessGrant extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'section_id', 'teacher_id', 'granted_by',
        'valid_from', 'valid_until', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->valid_from->isPast()
            && $this->valid_until->isFuture();
    }
}