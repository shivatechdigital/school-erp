<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasTeacherQuickActions;
use App\Models\TimetableExchangeRequest;
use Filament\Pages\Page;

class TeacherTimetableExchange extends Page
{
    use HasTeacherQuickActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Teacher';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Timetable Exchange';

    protected static ?string $title = 'Timetable Exchange Requests';

    protected string $view = 'filament.pages.teacher-timetable-exchange';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type === 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->requestExchangeAction(),
        ];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        return [
            'incomingExchanges' => TimetableExchangeRequest::query()
                ->with(['requester', 'requesterTimetable.schoolClass', 'requesterTimetable.section', 'requesterTimetable.subject'])
                ->where('recipient_id', $user->id)->where('status', 'pending')->latest()->get(),
            'outgoingExchanges' => TimetableExchangeRequest::query()
                ->with(['recipient', 'requesterTimetable.schoolClass', 'requesterTimetable.section', 'requesterTimetable.subject'])
                ->where('requester_id', $user->id)->latest()->limit(50)->get(),
        ];
    }
}
