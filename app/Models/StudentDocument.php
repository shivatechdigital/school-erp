<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StudentDocument extends Model
{
    protected $fillable = [
        'student_id', 'document_type', 'file_name', 'file_path', 'file_type', 'file_size',
        'is_verified', 'verified_at', 'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $query): void {
            $user = Auth::user();

            if ($user?->user_type === 'super_admin') {
                return;
            }

            if (! $user || ! $user->school_id) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereHas('student', fn (Builder $students) => $students->where('school_id', $user->school_id));
        });

        $assertStudentBelongsToUser = function (StudentDocument $document): void {
            $user = Auth::user();

            if ($user && $user->user_type !== 'super_admin'
                && ! Student::query()->whereKey($document->student_id)->exists()) {
                throw new AuthorizationException('The selected student is outside your school or branch.');
            }
        };

        static::creating($assertStudentBelongsToUser);
        static::updating($assertStudentBelongsToUser);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
