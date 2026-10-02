<?php

namespace App\Observers;

use App\Models\Exam;
use App\Models\GradingPolicy;
use App\Models\Mark;
use App\Models\MarkAudit;
use WeakMap;

class MarkObserver
{
    private WeakMap $oldValues;

    public function __construct()
    {
        $this->oldValues = new WeakMap;
    }

    public function saving(Mark $mark): void
    {
        $policy = GradingPolicy::query()
            ->where('school_id', $mark->school_id ?: Exam::query()->whereKey($mark->exam_id)->value('school_id'))
            ->where('branch_id', $mark->student?->branch_id)
            ->first();
        $mark->calculateResult($policy ? (float) $policy->subject_pass_percentage : null);

        if (! $mark->entered_by) {
            $mark->entered_by = auth()->id();
            $mark->entered_at = now();
        }

        // school_id is NOT NULL; super admin has no school, so take it from the exam.
        if (! $mark->school_id) {
            $mark->school_id = Exam::query()->whereKey($mark->exam_id)->value('school_id');
        }
    }

    public function updating(Mark $mark): void
    {
        $this->oldValues[$mark] = array_intersect_key(
            $mark->getOriginal(),
            array_flip(['theory_marks', 'practical_marks', 'total_marks', 'result', 'remark'])
        );
    }

    public function created(Mark $mark): void
    {
        $this->writeAudit($mark, null);
    }

    public function updated(Mark $mark): void
    {
        $oldValues = $this->oldValues[$mark] ?? [];
        unset($this->oldValues[$mark]);

        $newValues = $mark->only(['theory_marks', 'practical_marks', 'total_marks', 'result', 'remark']);
        if ($oldValues !== $newValues) {
            $this->writeAudit($mark, $oldValues);
        }
    }

    private function writeAudit(Mark $mark, ?array $oldValues): void
    {
        MarkAudit::query()->create([
            'mark_id' => $mark->id,
            'changed_by' => auth()->id() ?? $mark->entered_by,
            'old_values' => $oldValues,
            'new_values' => $mark->only(['theory_marks', 'practical_marks', 'total_marks', 'result', 'remark']),
            'changed_at' => now(),
        ]);
    }
}
