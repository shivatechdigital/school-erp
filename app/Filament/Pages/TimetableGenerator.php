<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Timetables\Schemas\TimetableForm;
use App\Filament\Resources\Timetables\TimetableResource;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Services\TimetableGeneratorService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use RuntimeException;

class TimetableGenerator extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Timetable Generator';

    protected static ?string $title = 'Auto-Generate Timetable';

    protected string $view = 'filament.pages.timetable-generator';

    public ?int $lastPlaced = null;

    /** @var array<int, string> */
    public array $lastNotes = [];

    public ?string $lastClassName = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Timetable')
                ->icon('heroicon-o-sparkles')
                ->modalHeading('Auto-Generate Timetable')
                ->modalDescription('Class ke saare active sections ke liye, har subject ke periods_per_week ke hisaab se clash-free weekly timetable ban jayega (koi teacher ek hi time pe do jagah book nahi hoga).')
                ->modalSubmitActionLabel('Generate')
                ->form([
                    Select::make('academic_year_id')
                        ->label('Academic Year')
                        ->options(fn (): array => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                        ->default(fn () => AcademicYear::query()->where('is_current', true)->value('id'))
                        ->required(),
                    Select::make('class_id')
                        ->label('Class')
                        ->options(fn (): array => SchoolClass::query()->where('is_active', true)->orderBy('sort_order')->pluck('name', 'id')->all())
                        ->required(),
                    CheckboxList::make('working_days')
                        ->label('Working Days')
                        ->options(TimetableForm::DAYS)
                        ->default(array_keys(TimetableForm::DAYS))
                        ->columns(3)
                        ->required(),
                    TextInput::make('periods_per_day')
                        ->label('Teaching periods per day')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(15)
                        ->default(8)
                        ->required(),
                    TextInput::make('period_duration')
                        ->label('Period duration (minutes)')
                        ->numeric()
                        ->minValue(10)
                        ->maxValue(120)
                        ->default(40)
                        ->required(),
                    TimePicker::make('day_start_time')
                        ->label('Day starts at')
                        ->seconds(false)
                        ->default('08:00')
                        ->required(),
                    Select::make('break_after')
                        ->label('Insert a break after these periods')
                        ->multiple()
                        ->options(fn (): array => array_combine(range(1, 14), range(1, 14)))
                        ->default([2, 5])
                        ->helperText('e.g. [2, 5] = ek break period 2 ke baad aur ek period 5 ke baad.'),
                    TextInput::make('break_duration')
                        ->label('Break duration (minutes)')
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(60)
                        ->default(15)
                        ->required(),
                    Toggle::make('replace_existing')
                        ->label('Replace existing timetable for this class')
                        ->helperText('On rakhein toh is class ke sections ka current timetable delete karke naya ban jayega.')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $academicYear = AcademicYear::query()->findOrFail($data['academic_year_id']);
                    $schoolClass = SchoolClass::query()->findOrFail($data['class_id']);

                    try {
                        $result = (new TimetableGeneratorService)->generate($schoolClass, $academicYear, [
                            'working_days' => $data['working_days'],
                            'periods_per_day' => (int) $data['periods_per_day'],
                            'period_duration' => (int) $data['period_duration'],
                            'day_start_time' => $data['day_start_time'],
                            'break_after' => array_map('intval', $data['break_after'] ?? []),
                            'break_duration' => (int) $data['break_duration'],
                            'replace_existing' => (bool) $data['replace_existing'],
                        ]);
                    } catch (RuntimeException $exception) {
                        Notification::make()->danger()->title('Could not generate timetable')->body($exception->getMessage())->send();

                        return;
                    }

                    $this->lastPlaced = $result['placed'];
                    $this->lastNotes = $result['notes'];
                    $this->lastClassName = $schoolClass->name;

                    Notification::make()
                        ->success()
                        ->title("Timetable generated for {$schoolClass->name}: {$result['placed']} periods placed")
                        ->body($result['notes'] ? implode("\n", array_slice($result['notes'], 0, 10)) : null)
                        ->persistent()
                        ->send();
                }),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'timetableUrl' => TimetableResource::getUrl('index'),
        ];
    }
}
