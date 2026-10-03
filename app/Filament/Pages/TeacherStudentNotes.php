<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\StudentNote;
use Filament\Pages\Page;

class TeacherStudentNotes extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Student Notes';

    protected static ?string $title = 'Student Notes';

    protected string $view = 'filament.pages.teacher-student-notes';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->addStudentNoteAction(),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'notes' => StudentNote::query()->with('student')
                ->where('author_id', auth()->id())
                ->latest()->limit(100)->get(),
        ];
    }
}
