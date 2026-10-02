<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'academic_year_id',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'substitute_for_id',
        'exchange_request_id',
        'day_of_week',
        'period_number',
        'start_time',
        'end_time',
        'room_no',
        'is_break',
        'break_label',
    ];

    protected $casts = [
        'is_break' => 'boolean',
        'period_number' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function substituteFor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_for_id');
    }

    /**
     * Slots on the same day whose time range overlaps [start, end). Back-to-back periods do not clash.
     */
    public function scopeOverlapping(Builder $query, string $day, string $startTime, string $endTime, ?int $ignoreId = null): Builder
    {
        return $query
            ->where('day_of_week', $day)
            ->where('start_time', '<', date('H:i:s', strtotime($endTime)))
            ->where('end_time', '>', date('H:i:s', strtotime($startTime)))
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId));
    }
}
