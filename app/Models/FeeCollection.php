<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class FeeCollection extends Model
{
    use BelongsToSchool, BelongsToBranch;

    protected $fillable = [
        'school_id', 'branch_id', 'student_id', 'fee_assignment_id',
        'receipt_no', 'amount', 'fine_amount', 'total_amount',
        'payment_mode', 'transaction_id', 'cheque_no', 'bank_name',
        'payment_date', 'remark', 'collected_by', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fine_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeAssignment()
    {
        return $this->belongsTo(FeeAssignment::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public static function generateReceiptNo(): string
    {
        $prefix = 'RCP-'.date('Y').'-';

        // receipt_no is unique across all schools, so the sequence must ignore tenant scopes.
        $last = static::withoutGlobalScopes()
            ->where('receipt_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $number = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
