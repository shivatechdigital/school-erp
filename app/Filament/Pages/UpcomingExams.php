<?php

namespace App\Filament\Pages;

use App\Models\Exam;
use Filament\Pages\Page;

class UpcomingExams extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Examination';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Upcoming Exams';

    protected static ?string $title = 'Upcoming Exams';

    protected string $view = 'filament.pages.upcoming-exams';

    protected function getViewData(): array
    {
        $user = auth()->user();

        $exams = Exam::query()
            ->with(['schedules.class', 'schedules.subject'])
            ->where('school_id', $user->school_id)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->orderBy('start_date')
            ->get();

        return ['exams' => $exams];
    }
}
