<?php

namespace App\Filament\Resources\Timetables\Schemas;

use App\Models\AcademicYear;
use App\Models\Timetable;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TimetableForm
{
    public const DAYS = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
    ];

    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Class & Schedule Slot')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->relationship('academicYear', 'name')
                            ->default(fn () => AcademicYear::query()->where('is_current', true)->value('id'))
                            ->required(),

                        Select::make('day_of_week')
                            ->label('Day')
                            ->options(self::DAYS)
                            ->required(),

                        Select::make('class_id')
                            ->label('Class')
                            ->relationship('schoolClass', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                        Select::make('section_id')
                            ->label('Section')
                            ->relationship('section', 'name', fn (Builder $query, Get $get) => $query->where('class_id', $get('class_id')))
                            ->required()
                            ->rules([self::clashRule('section_id', 'This class/section already has a period at this time slot.')]),
                    ])->columns(4),

                Section::make('Timing & Details')
                    ->schema([
                        Toggle::make('is_break')
                            ->label('Is Break / Assembly / Recess?')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('break_label')
                            ->label('Break Title')
                            ->placeholder('e.g. Lunch Break, Morning Assembly')
                            ->maxLength(100)
                            ->visible(fn (Get $get): bool => (bool) $get('is_break'))
                            ->required(fn (Get $get): bool => (bool) $get('is_break')),

                        TextInput::make('period_number')
                            ->label('Period Number')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required(),

                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->seconds(false)
                            ->required()
                            ->after('start_time'),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => ! $get('is_break'))
                            ->required(fn (Get $get): bool => ! $get('is_break')),

                        Select::make('teacher_id')
                            ->label('Teacher / Faculty')
                            ->relationship('teacher', 'name', fn (Builder $query) => $query
                                ->sameSchool()
                                ->whereIn('user_type', ['teacher', 'school_admin', 'branch_admin']))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => ! $get('is_break'))
                            ->required(fn (Get $get): bool => ! $get('is_break'))
                            ->rules([self::clashRule('teacher_id', 'Teacher is already booked for another class at this time slot.')]),

                        TextInput::make('room_no')
                            ->label('Room / Lab No.')
                            ->placeholder('e.g. Room 102, Physics Lab')
                            ->maxLength(50)
                            ->rules([self::clashRule('room_no', 'This room is already booked at this time slot.')]),
                    ])->columns(3),
            ]);
    }

    private static function clashRule(string $column, string $message): Closure
    {
        return fn (Get $get, ?Timetable $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record, $column, $message): void {
            $day = $get('day_of_week');
            $start = $get('start_time');
            $end = $get('end_time');

            if (blank($value) || ! $day || ! $start || ! $end) {
                return;
            }

            // Breaks only block the class/section itself, not teachers or rooms.
            if ($column !== 'section_id' && $get('is_break')) {
                return;
            }

            $clash = Timetable::query()
                ->overlapping($day, $start, $end, $record?->getKey())
                ->where($column, $value)
                ->when($get('academic_year_id'), fn (Builder $q, $yearId) => $q->where('academic_year_id', $yearId))
                ->when($get('school_id'), fn (Builder $q, $schoolId) => $q->where('school_id', $schoolId))
                ->when($column !== 'section_id', fn (Builder $q) => $q->where('is_break', false))
                ->exists();

            if ($clash) {
                $fail($message);
            }
        };
    }
}
