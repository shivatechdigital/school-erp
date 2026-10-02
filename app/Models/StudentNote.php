<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = ['school_id', 'branch_id', 'student_id', 'author_id', 'note', 'visibility'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}