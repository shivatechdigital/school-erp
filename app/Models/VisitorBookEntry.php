<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class VisitorBookEntry extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'created_by', 'purpose', 'number_of_people', 'people',
        'phone', 'visit_date', 'in_time', 'out_time', 'documents',
    ];

    protected function casts(): array
    {
        return [
            'number_of_people' => 'integer',
            'people' => 'array',
            'visit_date' => 'date',
            'documents' => 'array',
        ];
    }
}
