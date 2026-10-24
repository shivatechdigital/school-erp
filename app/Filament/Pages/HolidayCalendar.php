<?php

namespace App\Filament\Pages;

use App\Models\AcademicYear;
use App\Models\Holiday;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class HolidayCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'HR';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Holiday Calendar';

    protected static ?string $title = 'Holiday Calendar';

    protected string $view = 'filament.pages.holiday-calendar';

    public string $month = '';

    public function mount(): void
    {
        [$min, $max] = $this->sessionBounds();
        $month = now()->startOfMonth();

        if ($min && $month->lt($min)) {
            $month = $min->copy();
        } elseif ($max && $month->gt($max)) {
            $month = $max->copy();
        }

        $this->month = $month->format('Y-m');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    public function previousMonth(): void
    {
        [$min] = $this->sessionBounds();
        $target = Carbon::parse($this->month.'-01')->subMonthNoOverflow();

        if ($min && $target->lt($min)) {
            return;
        }

        $this->month = $target->format('Y-m');
    }

    public function nextMonth(): void
    {
        [, $max] = $this->sessionBounds();
        $target = Carbon::parse($this->month.'-01')->addMonthNoOverflow();

        if ($max && $target->gt($max)) {
            return;
        }

        $this->month = $target->format('Y-m');
    }

    public function goToToday(): void
    {
        [$min, $max] = $this->sessionBounds();
        $today = now()->startOfMonth();

        if ($min && $today->lt($min)) {
            $today = $min->copy();
        } elseif ($max && $today->gt($max)) {
            $today = $max->copy();
        }

        $this->month = $today->format('Y-m');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->holidayRangeAction(),
        ];
    }

    /** Marks a whole continuous span (e.g. winter vacation) as a holiday in one go. */
    protected function holidayRangeAction(): Action
    {
        return Action::make('holidayRange')
            ->label('Add Holiday Range')
            ->icon('heroicon-o-calendar-days')
            ->modalHeading('Add Holiday Range')
            ->modalDescription('e.g. Winter Vacation, Summer Break — ek saath poore hafte/hafton ka holiday set karein.')
            ->modalSubmitActionLabel('Save')
            ->form([
                TextInput::make('holiday_name')
                    ->label('Holiday name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Winter Vacation'),
                DatePicker::make('start_date')
                    ->label('From')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label('To')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                Textarea::make('description')
                    ->label('Notes (optional)')
                    ->rows(2),
            ])
            ->action(function (array $data): void {
                $start = Carbon::parse($data['start_date'])->startOfDay();
                $end = Carbon::parse($data['end_date'])->startOfDay();

                if ($end->diffInDays($start) > 180) {
                    Notification::make()->danger()->title('Range is too long (max 180 days)')->send();

                    return;
                }

                for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                    $this->upsertHoliday($day->toDateString(), $data['holiday_name'], $data['description'] ?? null);
                }

                $this->month = $start->format('Y-m');

                Notification::make()->success()->title('Holiday range saved')->send();
            });
    }

    private function upsertHoliday(string $date, string $title, ?string $description): void
    {
        $existing = $this->holidayFor($date);
        $attributes = ['title' => $title, 'description' => $description];

        if ($existing) {
            $existing->update($attributes);
        } else {
            Holiday::query()->create($attributes + ['school_id' => auth()->user()->school_id, 'date' => $date]);
        }
    }

    /**
     * Not listed in getHeaderActions() on purpose: it's only ever opened by clicking a
     * calendar day cell (via mountAction), so no generic button renders in the header.
     */
    protected function manageHolidayAction(): Action
    {
        return Action::make('manageHoliday')
            ->modalHeading(fn (array $arguments): string => Carbon::parse($arguments['date'])->format('l, d F Y'))
            ->modalSubmitActionLabel('Save')
            ->modalDescription('Holiday ka naam likh kar save karein. Hatane ke liye naam blank chhodkar save karein.')
            ->fillForm(function (array $arguments): array {
                $holiday = $this->holidayFor($arguments['date']);

                return [
                    'holiday_name' => $holiday?->title,
                    'description' => $holiday?->description,
                ];
            })
            ->form([
                TextInput::make('holiday_name')
                    ->label('Holiday name')
                    ->maxLength(255)
                    ->placeholder('e.g. Diwali, Republic Day'),
                Textarea::make('description')
                    ->label('Notes (optional)')
                    ->rows(2),
            ])
            ->action(function (array $data, array $arguments): void {
                $title = trim((string) ($data['holiday_name'] ?? ''));
                $existing = $this->holidayFor($arguments['date']);

                if ($title === '') {
                    if ($existing) {
                        $existing->delete();
                        Notification::make()->success()->title('Holiday removed')->send();
                    }

                    return;
                }

                $this->upsertHoliday($arguments['date'], $title, $data['description'] ?? null);

                Notification::make()->success()->title('Holiday saved')->send();
            });
    }

    protected function getViewData(): array
    {
        $start = Carbon::parse($this->month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        [$min, $max] = $this->sessionBounds();

        $holidays = Holiday::query()
            ->where('school_id', auth()->user()->school_id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->get()
            ->keyBy(fn (Holiday $holiday): string => $holiday->date->toDateString());

        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = [
                'date' => $day->copy(),
                'isToday' => $day->isToday(),
                'isWeekend' => $day->isWeekend(),
                'holiday' => $holidays->get($day->toDateString()),
            ];
        }

        return [
            'monthLabel' => $start->format('F Y'),
            'leadingBlankDays' => $start->dayOfWeek,
            'days' => $days,
            'canGoPrevious' => ! $min || $start->copy()->subMonthNoOverflow()->gte($min),
            'canGoNext' => ! $max || $start->copy()->addMonthNoOverflow()->lte($max),
            'session' => $this->currentSession(),
        ];
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    private function sessionBounds(): array
    {
        $session = $this->currentSession();

        if (! $session) {
            return [null, null];
        }

        return [$session->start_date->copy()->startOfMonth(), $session->end_date->copy()->startOfMonth()];
    }

    private function currentSession(): ?AcademicYear
    {
        $schoolId = auth()->user()->school_id;

        if (! $schoolId) {
            return null;
        }

        return AcademicYear::query()->where('school_id', $schoolId)->where('is_current', true)->first();
    }

    private function holidayFor(string $date): ?Holiday
    {
        return Holiday::query()
            ->where('school_id', auth()->user()->school_id)
            ->whereDate('date', $date)
            ->first();
    }
}
