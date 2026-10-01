<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'school_id', 'branch_id', 'academic_year_id',
        'class_id', 'section_id', 'user_id',
        'admission_no', 'roll_no', 'first_name',
        'middle_name', 'last_name', 'gender',
        'date_of_birth', 'blood_group', 'category',
        'admission_date', 'status',
        // ... all fields
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    // Relationships
    public function school() { return $this->belongsTo(School::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function class() { return $this->belongsTo(SchoolClass::class); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function guardians() { return $this->belongsToMany(Guardian::class); }
    public function documents() { return $this->hasMany(StudentDocument::class); }

    // Global Scope (Multi-tenancy) - baad mein lagayenge
}