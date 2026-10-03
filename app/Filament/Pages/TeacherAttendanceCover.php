<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\AttendanceAccessGrant;
use Filament\Pages\Page;

class TeacherAttendanceCover extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Attendance Cover';

    protected static ?string $title = 'Temporary Attendance Cover';

    protected string $view = 'filament.pages.teacher-attendance-cover';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->delegateAttendanceAction(),
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        return [
            'activeGrants' => AttendanceAccessGrant::query()
                ->with(['section.class', 'teacher'])
                ->where('granted_by', $user->id)->whereNull('revoked_at')->where('valid_until', '>', now())
                ->latest()->get(),
        ];
    }
}
