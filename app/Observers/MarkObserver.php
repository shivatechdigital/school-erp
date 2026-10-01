<?php

namespace App\Observers;

use App\Models\Exam;
use App\Models\Mark;

class MarkObserver
{
    public function saving(Mark $mark): void
    {
        $mark->calculateResult();

        if (! $mark->entered_by) {
            $mark->entered_by = auth()->id();
            $mark->entered_at = now();
        }

        // school_id is NOT NULL; super admin has no school, so take it from the exam.
        if (! $mark->school_id) {
            $mark->school_id = Exam::query()->whereKey($mark->exam_id)->value('school_id');
        }
    }
}
