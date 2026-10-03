<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use Filament\Pages\Page;

class LowAttendanceStaff extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|\UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Low Attendance (Staff)';

    protected static ?string $title = 'Staff Below 75% Attendance';

    protected string $view = 'filament.pages.low-attendance-staff';

    public const THRESHOLD = 75.0;

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        $rows = User::query()
            ->whereIn('user_type', StaffResource::STAFF_TYPES)
            ->where('status', 'active')
            ->where('school_id', $user->school_id)
            ->when($user->branch_id, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->withCount([
                'staffAttendances as total_marked' => fn ($query) => $query->whereYear('date', now()->year)->where('status', '!=', 'holiday'),
                'staffAttendances as present_count' => fn ($query) => $query->whereYear('date', now()->year)
                    ->whereIn('status', ['present', 'late', 'half_day']),
            ])
            ->get()
            ->map(fn (User $member): array => [
                'user' => $member,
                'percentage' => $member->total_marked > 0 ? round($member->present_count / $member->total_marked * 100, 1) : null,
                'total_marked' => $member->total_marked,
            ])
            ->filter(fn (array $row): bool => $row['percentage'] !== null && $row['percentage'] < self::THRESHOLD)
            ->sortBy('percentage')
            ->values();

        return ['rows' => $rows];
    }
}
