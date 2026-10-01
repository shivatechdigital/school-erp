<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'logo', 'email', 'phone', 'alternate_phone', 'website',
        'address', 'city', 'state', 'pincode', 'country', 'board', 'affiliation_no',
        'recognition_no', 'type', 'medium', 'established_year', 'status', 'domain',
        'subdomain', 'modules_enabled', 'storage_limit_mb', 'student_limit',
        'primary_color', 'secondary_color', 'activated_at', 'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'modules_enabled' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function currentAcademicYear()
    {
        return $this->academicYears()->where('is_current', true)->first();
    }

    public function activeStudentsCount(): int
    {
        return $this->students()->where('status', 'active')->count();
    }
}
