<?php

namespace App\Observers;

use App\Models\FeeCollection;

class FeeCollectionObserver
{
    public function creating(FeeCollection $collection): void
    {
        // Auto calculate total
        $collection->total_amount = (float) $collection->amount + (float) $collection->fine_amount;

        // Auto set collected_by
        if (! $collection->collected_by) {
            $collection->collected_by = auth()->id();
        }

        // Auto set school/branch from the student (branch_id is NOT NULL; school admins have no branch)
        $student = $collection->student;

        if (! $collection->school_id && $student) {
            $collection->school_id = $student->school_id;
        }

        if (! $collection->branch_id && $student) {
            $collection->branch_id = $student->branch_id;
        }
    }
}
