<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'user_id', 'date',
        'status', 'check_in', 'check_out',
        'total_hours', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_hours' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
