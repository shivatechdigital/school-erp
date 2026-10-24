<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = ['school_id', 'branch_id', 'created_by', 'name', 'image'];
}
