<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use BelongsToBranch, BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'branch_id',
        'published_by',
        'title',
        'content',
        'priority',
        'target_audience',
        'class_id',
        'section_id',
        'publish_date',
        'expiry_date',
        'attachments',
        'is_published',
        'send_notification',
    ];

    protected $casts = [
        'attachments' => 'array',
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'is_published' => 'boolean',
        'send_notification' => 'boolean',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
