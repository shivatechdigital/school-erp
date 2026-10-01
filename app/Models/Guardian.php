<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'user_id', 'name', 'relation', 'gender', 'date_of_birth', 'photo',
        'phone', 'alternate_phone', 'email', 'whatsapp', 'occupation', 'company_name',
        'annual_income', 'address', 'city', 'state', 'pincode', 'aadhaar_no', 'pan_no',
        'is_primary_contact', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'annual_income' => 'decimal:2',
            'is_primary_contact' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot('relation', 'is_primary')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
