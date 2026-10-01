<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Exam Details')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('name')
                            ->required()
                            ->placeholder('e.g., Unit Test 1, Mid Term, Final Exam'),

                        TextInput::make('code')
                            ->placeholder('e.g., UT1, MT, FA'),

                        Select::make('type')
                            ->options([
                                'unit_test' => 'Unit Test',
                                'mid_term' => 'Mid Term',
                                'final' => 'Final Exam',
                                'practical' => 'Practical',
                                'viva' => 'Viva',
                                'assignment' => 'Assignment',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('unit_test'),

                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->required()
                            ->label('Academic Year'),

                        TextInput::make('total_marks')
                            ->numeric()
                            ->default(100)
                            ->label('Total Marks'),

                        TextInput::make('pass_marks')
                            ->numeric()
                            ->default(33)
                            ->label('Pass Marks'),

                        TextInput::make('weightage')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->helperText('Final result mein kitna % weightage'),

                        DatePicker::make('start_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('upcoming'),

                        Toggle::make('result_published')
                            ->label('Result Published?'),
                    ])->columns(3),
            ]);
    }
}
