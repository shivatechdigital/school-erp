<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'must_change_password', 'school_id', 'branch_id', 'employee_code', 'phone',
    'alternate_phone', 'profile_photo', 'gender', 'date_of_birth', 'blood_group', 'address',
    'city', 'state', 'pincode', 'qualification', 'designation', 'department', 'department_id', 'joining_date',
    'salary', 'user_type', 'status', 'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'salary' => 'decimal:2',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->user_type === 'super_admin') {
            return true;
        }

        return $this->status === 'active' && in_array($this->user_type, [
            'school_admin', 'branch_admin', 'teacher', 'accountant', 'librarian',
            'transport_manager', 'receptionist', 'hr', 'doctor',
        ], true);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function staffAttendances()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function departmentRecord()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // User has no tenant global scope; restrict to the logged-in user's school/branch.
    public function scopeSameSchool(Builder $query): Builder
    {
        $user = auth()->user();

        return $query->when($user?->user_type !== 'super_admin', fn (Builder $q) => $q
            ->where('school_id', $user?->school_id)
            ->when($user?->branch_id, fn (Builder $q, $branchId) => $q->where('branch_id', $branchId)));
    }
}
