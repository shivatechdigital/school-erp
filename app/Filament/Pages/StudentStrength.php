<?php

namespace App\Filament\Pages;

use App\Models\Section;
use Filament\Pages\Page;

class StudentStrength extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Student Strength';

    protected static ?string $title = 'Student Strength';

    protected string $view = 'filament.pages.student-strength';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $sections = Section::query()
            ->with('class')
            ->withCount(['students' => fn ($query) => $query->where('status', 'active')])
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn (Section $a, Section $b) => ($a->class?->sort_order ?? 0) <=> ($b->class?->sort_order ?? 0),
                fn (Section $a, Section $b) => $a->name <=> $b->name,
            ]);

        $byClass = $sections->groupBy(fn (Section $section) => $section->class?->name ?? 'Unassigned');

        return [
            'byClass' => $byClass,
            'grandTotal' => $sections->sum('students_count'),
        ];
    }
}
