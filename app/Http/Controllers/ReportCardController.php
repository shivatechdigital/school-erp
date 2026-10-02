<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\GradingPolicy;
use App\Models\Mark;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    public function generate(Student $student, Exam $exam)
    {
        abort_unless(auth()->user()?->user_type === 'super_admin'
            || auth()->user()?->school_id === $student->school_id, 404);
        abort_unless($exam->school_id === $student->school_id, 404);

        // Student ke marks is exam mein
        $marks = Mark::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();

        // Total & Percentage
        $totalObtained = (float) $marks->sum('total_marks');
        $totalMax = (float) $marks->sum('max_marks');
        $overallPercentage = $totalMax > 0
            ? round(($totalObtained / $totalMax) * 100, 2)
            : 0;

        // Overall Grade
        $overallGrade = Mark::gradeFor((float) $overallPercentage);

        $policy = GradingPolicy::query()
            ->where('school_id', $student->school_id)
            ->where('branch_id', $student->branch_id)
            ->first();
        $subjectPassPercentage = (float) ($policy?->subject_pass_percentage ?? 0);
        foreach ($marks as $mark) {
            if (! in_array($mark->result, ['absent', 'withheld'], true)
                && ($policy
                    ? (float) $mark->percentage < $subjectPassPercentage
                    : (float) $mark->total_marks < (float) $mark->pass_marks)) {
                $mark->result = 'fail';
            }
        }

        $overallPassPercentage = (float) ($policy?->overall_pass_percentage ?? 0);
        $expectedSubjects = ExamSchedule::query()->where('exam_id', $exam->id)->where('class_id', $student->class_id)->count();
        $isComplete = $expectedSubjects > 0 && $marks->count() >= $expectedSubjects;
        $overallResult = ! $isComplete
            ? 'INCOMPLETE'
            : ($marks->contains('result', 'fail')
            || $overallPercentage < $overallPassPercentage
                ? 'FAIL'
                : 'PASS');

        // Rank (class mein) — har student ke sabhi subjects ka total jod kar
        $position = Mark::where('exam_id', $exam->id)
            ->where('class_id', $student->class_id)
            ->whereNotNull('total_marks')
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(total_marks) as grand_total')
            ->orderByDesc('grand_total')
            ->pluck('student_id')
            ->search($student->id);
        $rank = $position === false ? '—' : $position + 1;

        $student->load(['class', 'section', 'guardians', 'school']);
        $exam->load('academicYear');

        $data = [
            'student' => $student,
            'exam' => $exam,
            'marks' => $marks,
            'totalObtained' => $totalObtained,
            'totalMax' => $totalMax,
            'overallPercentage' => $overallPercentage,
            'overallGrade' => $overallGrade,
            'overallResult' => $overallResult,
            'rank' => $rank,
            'school' => $student->school,
        ];

        $pdf = Pdf::loadView('pdf.report-card', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true);

        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', "ReportCard_{$student->admission_no}_{$exam->code}");

        return $pdf->download("{$fileName}.pdf");
    }
}
