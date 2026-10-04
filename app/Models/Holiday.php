<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'date', 'title', 'description'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
