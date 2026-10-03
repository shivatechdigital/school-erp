<?php

namespace App\Filament\Pages;

use App\Models\Leave;
use Filament\Pages\Page;

class LeavesCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Leaves Calendar';

    protected static ?string $title = 'Leaves Calendar';

    protected string $view = 'filament.pages.leaves-calendar';

    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    public function previousMonth(): void
    {
        $this->month = \Illuminate\Support\Carbon::parse($this->month.'-01')->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = \Illuminate\Support\Carbon::parse($this->month.'-01')->addMonthNoOverflow()->format('Y-m');
    }

    protected function getViewData(): array
    {
        $start = \Illuminate\Support\Carbon::parse($this->month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $leaves = Leave::query()
            ->with(['user', 'leaveType'])
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $end)
            ->whereDate('to_date', '>=', $start)
            ->get();

        $byDate = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key = $day->toDateString();
            $onLeave = $leaves->filter(fn (Leave $leave): bool => $day->between($leave->from_date, $leave->to_date));
            if ($onLeave->isNotEmpty()) {
                $byDate[$key] = [
                    'date' => $day->copy(),
                    'leaves' => $onLeave->values(),
                ];
            }
        }

        return [
            'monthLabel' => $start->format('F Y'),
            'byDate' => $byDate,
        ];
    }
}
