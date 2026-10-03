<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\Leave;
use App\Models\LeaveType;
use Filament\Pages\Page;

class TeacherLeave extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Leave';

    protected static ?string $title = 'My Leave';

    protected string $view = 'filament.pages.teacher-leave';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->requestLeaveAction(),
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $year = now()->year;

        $leaveTypes = LeaveType::query()->where('school_id', $user->school_id)->where('is_active', true)->orderBy('name')->get();

        $leaves = Leave::query()->with('leaveType')
            ->where('user_id', $user->id)
            ->whereYear('from_date', $year)
            ->latest('from_date')->get();

        $usedByType = $leaves->where('status', 'approved')->groupBy('leave_type_id')->map->sum('total_days');
        $pendingByType = $leaves->where('status', 'pending')->groupBy('leave_type_id')->map->sum('total_days');

        $breakdown = $leaveTypes->map(function (LeaveType $type) use ($usedByType, $pendingByType): array {
            $used = (int) ($usedByType[$type->id] ?? 0);
            $pending = (int) ($pendingByType[$type->id] ?? 0);

            return [
                'type' => $type,
                'allocated' => (int) $type->max_days,
                'used' => $used,
                'pending' => $pending,
                'remaining' => max(0, (int) $type->max_days - $used),
            ];
        });

        return [
            'year' => $year,
            'breakdown' => $breakdown,
            'totalAllocated' => (int) $leaveTypes->sum('max_days'),
            'totalUsed' => (int) $usedByType->sum(),
            'totalPending' => (int) $pendingByType->sum(),
            'totalRemaining' => max(0, (int) $leaveTypes->sum('max_days') - (int) $usedByType->sum()),
            'leaves' => $leaves,
        ];
    }
}
