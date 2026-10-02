<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class TimetableExchangeRequest extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'requester_id', 'recipient_id', 'requester_timetable_id',
        'recipient_timetable_id', 'reason', 'status', 'response_note', 'responded_at',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function requesterTimetable()
    {
        return $this->belongsTo(Timetable::class, 'requester_timetable_id');
    }

    public function recipientTimetable()
    {
        return $this->belongsTo(Timetable::class, 'recipient_timetable_id');
    }
}