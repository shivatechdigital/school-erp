<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->user_type === 'super_admin') {
            return [
                Stat::make('Total Schools', School::count())
                    ->icon('heroicon-o-building-office-2')
                    ->color('primary'),
                Stat::make('Total Students', Student::count())
                    ->icon('heroicon-o-academic-cap')
                    ->color('success'),
                Stat::make('Total Teaching Staff', User::query()->where('user_type', 'teacher')->count())
                    ->icon('heroicon-o-users')
                    ->color('info'),
                Stat::make('Revenue This Month', 'INR 0')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning'),
            ];
        }

        $schoolUsers = User::query()
            ->where('school_id', $user->school_id)
            ->when($user->branch_id, fn ($query) => $query->where('branch_id', $user->branch_id));

        return [
            Stat::make('Active Students', Student::query()->where('status', 'active')->count())
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            Stat::make('Active Teachers', (clone $schoolUsers)->where('user_type', 'teacher')->where('status', 'active')->count())
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('Active Sections', Section::query()->where('is_active', true)->count())
                ->icon('heroicon-o-squares-2x2')
                ->color('info'),
            Stat::make("Today's Attendance", '85%')
                ->icon('heroicon-o-calendar-days')
                ->color('warning'),
        ];
    }
}