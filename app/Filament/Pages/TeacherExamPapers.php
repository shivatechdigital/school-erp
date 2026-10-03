<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\ExamPaper;
use Filament\Pages\Page;

class TeacherExamPapers extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Exam Papers';

    protected static ?string $title = 'Exam Papers';

    protected string $view = 'filament.pages.teacher-exam-papers';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createExamPaperAction(),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'papers' => ExamPaper::query()->with(['schedule.exam', 'schedule.subject', 'schoolClass', 'section'])
                ->where('teacher_id', auth()->id())
                ->latest()->limit(100)->get(),
        ];
    }
}
