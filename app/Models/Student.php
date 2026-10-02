<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use SoftDeletes, HasFactory, BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'academic_year_id',
        'class_id', 'section_id', 'user_id',
        'admission_no', 'roll_no', 'gr_no', 'student_code', 'first_name',
        'middle_name', 'last_name', 'gender',
        'date_of_birth', 'birth_place', 'blood_group', 'religion', 'caste', 'category',
        'nationality', 'mother_tongue', 'aadhaar_no', 'samagra_id', 'photo', 'email', 'phone',
        'current_address', 'permanent_address', 'city', 'state', 'pincode', 'admission_date',
        'previous_school', 'previous_class', 'admission_type', 'medical_conditions', 'allergies',
        'is_transport', 'is_hostel', 'status', 'leaving_date', 'leaving_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'leaving_date' => 'date',
        'is_transport' => 'boolean',
        'is_hostel' => 'boolean',
    ];

    // Relationships
    public function class() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function guardians() { return $this->belongsToMany(Guardian::class, 'guardian_student')->withPivot('relation', 'is_primary')->withTimestamps(); }
    public function documents() { return $this->hasMany(StudentDocument::class); }
    public function notes() { return $this->hasMany(StudentNote::class); }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}