<?php

namespace App\Filament\Pages;

use App\Models\AcademicYear;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class AcademicSessionSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static string|\UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Academic Session';

    protected static ?string $title = 'Academic Session';

    protected string $view = 'filament.pages.academic-session-settings';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->user_type, ['school_admin', 'branch_admin'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editSession')
                ->label(fn (): string => $this->currentSession() ? 'Edit Session' : 'Set Session')
                ->icon('heroicon-o-pencil-square')
                ->fillForm(function (): array {
                    $session = $this->currentSession();

                    return [
                        'start_month' => $session?->start_date?->month ?? now()->month,
                        'start_year' => $session?->start_date?->year ?? now()->year,
                        'end_month' => $session?->end_date?->month ?? now()->month,
                        'end_year' => $session?->end_date?->year ?? now()->year + 1,
                    ];
                })
                ->form([
                    Select::make('start_month')
                        ->label('Session Start Month')
                        ->options(self::months())
                        ->required(),
                    Select::make('start_year')
                        ->label('Session Start Year')
                        ->options(self::years())
                        ->required(),
                    Select::make('end_month')
                        ->label('Session End Month')
                        ->options(self::months())
                        ->required(),
                    Select::make('end_year')
                        ->label('Session End Year')
                        ->options(self::years())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $start = Carbon::create((int) $data['start_year'], (int) $data['start_month'], 1)->startOfMonth();
                    $end = Carbon::create((int) $data['end_year'], (int) $data['end_month'], 1)->endOfMonth();

                    if ($end->lte($start)) {
                        Notification::make()->danger()->title('Session end must be after the session start')->send();

                        return;
                    }

                    $schoolId = auth()->user()->school_id;
                    $session = $this->currentSession();

                    AcademicYear::query()
                        ->where('school_id', $schoolId)
                        ->when($session, fn ($query) => $query->whereKeyNot($session->id))
                        ->update(['is_current' => false]);

                    $attributes = [
                        'name' => $start->format('M Y').' - '.$end->format('M Y'),
                        'start_date' => $start,
                        'end_date' => $end,
                        'is_current' => true,
                        'status' => 'active',
                    ];

                    if ($session) {
                        $session->update($attributes);
                    } else {
                        AcademicYear::query()->create($attributes + ['school_id' => $schoolId]);
                    }

                    Notification::make()->success()->title('Academic session updated')->send();
                }),
        ];
    }

    protected function getViewData(): array
    {
        $session = $this->currentSession();

        return [
            'session' => $session,
            'progress' => $session ? self::sessionProgress($session->start_date, $session->end_date) : null,
        ];
    }

    private function currentSession(): ?AcademicYear
    {
        $schoolId = auth()->user()->school_id;

        if (! $schoolId) {
            return null;
        }

        return AcademicYear::query()->where('school_id', $schoolId)->where('is_current', true)->first();
    }

    private static function sessionProgress(Carbon $start, Carbon $end): array
    {
        $today = now();
        $today = $today->lt($start) ? $start->copy() : ($today->gt($end) ? $end->copy() : $today);
        $totalDays = max(1, $start->diffInDays($end));
        $elapsedDays = $start->diffInDays($today);

        return [
            'percent' => (int) round(min(100, max(0, ($elapsedDays / $totalDays) * 100))),
            'daysRemaining' => max(0, (int) now()->startOfDay()->diffInDays($end, false)),
        ];
    }

    private static function months(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
    }

    private static function years(): array
    {
        return collect(range(now()->year - 2, now()->year + 5))->mapWithKeys(fn (int $year): array => [$year => $year])->all();
    }
}
