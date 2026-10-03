<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\Notice;
use Filament\Pages\Page;

class TeacherClassNotice extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Class Notices';

    protected static ?string $title = 'Class Notices';

    protected string $view = 'filament.pages.teacher-class-notice';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->publishNoticeAction(),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'notices' => Notice::query()->with(['schoolClass', 'section'])
                ->where('published_by', auth()->id())
                ->latest()->limit(100)->get(),
        ];
    }
}
