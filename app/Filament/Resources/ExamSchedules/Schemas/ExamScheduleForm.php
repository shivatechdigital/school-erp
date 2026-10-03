<?php

namespace App\Filament\Resources\ExamSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Schedule')
                    ->schema([
                        Select::make('exam_id')
                            ->relationship('exam', 'name')
                            ->required()
                            ->label('Exam'),

                        Select::make('class_id')
                            ->relationship('class', 'name')
                            ->required()
                            ->label('Class'),

                        Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->required()
                            ->label('Subject'),

                        DatePicker::make('exam_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        TimePicker::make('start_time')
                            ->required()
                            ->format('H:i')
                            ->displayFormat('h:i A'),

                        TimePicker::make('end_time')
                            ->required()
                            ->format('H:i')
                            ->displayFormat('h:i A'),

                        TextInput::make('max_marks')
                            ->numeric()
                            ->default(100),

                        TextInput::make('pass_marks')
                            ->numeric()
                            ->default(33),

                        TextInput::make('room_no')
                            ->placeholder('e.g., Room 101'),

                        Textarea::make('instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
