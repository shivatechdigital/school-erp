<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\StaffAttendance;
use App\Models\User;
use Filament\Pages\Page;

class StaffAttendanceOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    protected static string|\UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Staff Attendance Overview';

    protected static ?string $title = 'Staff Attendance Overview';

    protected string $view = 'filament.pages.staff-attendance-overview';

    public string $date = '';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        $staff = User::query()
            ->whereIn('user_type', StaffResource::STAFF_TYPES)
            ->where('status', 'active')
            ->where('school_id', $user->school_id)
            ->when($user->branch_id, fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $attendanceByUser = StaffAttendance::query()
            ->whereDate('date', $this->date)
            ->whereIn('user_id', $staff->modelKeys())
            ->get()
            ->keyBy('user_id');

        $rows = $staff->map(fn (User $member): array => [
            'user' => $member,
            'attendance' => $attendanceByUser->get($member->id),
        ]);

        $counts = $rows->groupBy(fn (array $row): string => $row['attendance']?->status ?? 'not_marked')
            ->map->count();

        return [
            'rows' => $rows,
            'counts' => $counts,
            'total' => $staff->count(),
        ];
    }
}
