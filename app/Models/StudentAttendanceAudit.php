<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendanceAudit extends Model
{
    protected $fillable = [
        'student_attendance_id', 'changed_by', 'access_grant_id',
        'old_status', 'new_status', 'changed_at',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function attendance()
    {
        return $this->belongsTo(StudentAttendance::class, 'student_attendance_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function accessGrant()
    {
        return $this->belongsTo(AttendanceAccessGrant::class);
    }
}