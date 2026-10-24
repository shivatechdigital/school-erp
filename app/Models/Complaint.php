<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'created_by', 'complainant_name', 'against_name',
        'complaint_date', 'details', 'attachment',
    ];

    protected function casts(): array
    {
        return ['complaint_date' => 'date'];
    }
}
