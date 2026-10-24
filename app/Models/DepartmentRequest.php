<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DepartmentRequest extends Model
{
    use BelongsToBranch, BelongsToSchool;

    protected $fillable = [
        'school_id', 'branch_id', 'department_id', 'raised_by', 'type', 'title', 'description',
        'attachment', 'status', 'head_remarks', 'admin_remarks', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    /** Head and sub-head of this request's department, when assigned. */
    public function headRecipients(): Collection
    {
        $ids = array_filter([$this->department?->head_user_id, $this->department?->sub_head_user_id]);

        if ($ids === []) {
            return collect();
        }

        return User::query()->whereIn('id', $ids)->get();
    }

    /** School admins (all branches) plus the branch admin of this request's branch. */
    public function adminRecipients(): Collection
    {
        return User::query()
            ->where('school_id', $this->school_id)
            ->whereIn('user_type', ['school_admin', 'branch_admin'])
            ->where(function ($query): void {
                $query->where('user_type', 'school_admin')
                    ->orWhere('branch_id', $this->branch_id);
            })
            ->get();
    }
}
