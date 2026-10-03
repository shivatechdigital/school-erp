<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Edit Attendance')
                    ->schema([
                        Select::make('student_id')
                            ->relationship('student', 'first_name')
                            ->required()
                            ->disabled(),

                        DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled(),

                        Radio::make('status')
                            ->options([
                                'present' => '✅ Present',
                                'absent' => '❌ Absent',
                                'late' => '⏰ Late',
                                'half_day' => '🕐 Half Day',
                                'leave' => '📋 Leave',
                            ])
                            ->required()
                            ->inline(),

                        TextInput::make('remark')
                            ->placeholder('Optional remark'),
                    ])->columns(2),
            ]);
    }
}
