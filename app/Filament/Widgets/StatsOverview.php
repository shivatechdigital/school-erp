<?php

namespace App\Filament\Widgets;

use App\Models\FeeCollection;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentAttendance;
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
                Stat::make('Revenue This Month', 'INR '.number_format(
                    (float) FeeCollection::query()->where('status', 'success')
                        ->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month)
                        ->sum('total_amount'),
                    2
                ))
                    ->icon('heroicon-o-banknotes')
                    ->color('warning'),
            ];
        }

        $schoolUsers = User::query()
            ->where('school_id', $user->school_id)
            ->when($user->branch_id, fn ($query) => $query->where('branch_id', $user->branch_id));

        $activeStudents = Student::query()->where('status', 'active')->count();
        $presentToday = StudentAttendance::query()
            ->whereDate('date', today())->whereNull('period_number')
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->distinct('student_id')->count('student_id');
        $markedToday = StudentAttendance::query()
            ->whereDate('date', today())->whereNull('period_number')
            ->distinct('student_id')->count('student_id');

        $activeTeachers = (clone $schoolUsers)->where('user_type', 'teacher')->where('status', 'active')->count();
        $teachersPresentToday = \App\Models\StaffAttendance::query()
            ->whereDate('date', today())
            ->whereIn('user_id', (clone $schoolUsers)->where('user_type', 'teacher')->pluck('id'))
            ->whereIn('status', ['present', 'late', 'half_day'])
            ->count();

        $feeCollectedThisMonth = FeeCollection::query()->where('status', 'success')
            ->whereYear('payment_date', now()->year)->whereMonth('payment_date', now()->month)
            ->sum('total_amount');

        return [
            Stat::make('Active Students', $activeStudents)
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            Stat::make('Active Teachers', $activeTeachers)
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make("Today's Student Strength", "{$presentToday} / ".($markedToday ?: $activeStudents))
                ->description($markedToday > 0 ? 'Present / marked today' : 'Attendance not yet marked today')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
            Stat::make("Today's Teacher Strength", "{$teachersPresentToday} / {$activeTeachers}")
                ->description('Present today')
                ->icon('heroicon-o-user-group')
                ->color('warning'),
            Stat::make('Fee Collection This Month', 'INR '.number_format((float) $feeCollectedThisMonth, 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
