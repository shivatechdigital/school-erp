<?php

namespace App\Filament\Resources\Marks\Schemas;

use App\Models\ExamSchedule;
use App\Models\Student;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Enter Marks')
                    ->icon('heroicon-o-pencil')
                    ->schema([
                        Select::make('exam_id')
                            ->relationship('exam', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('exam_schedule_id', null);
                                $set('student_id', null);
                            }),

                        Select::make('exam_schedule_id')
                            ->label('Schedule (Class + Subject)')
                            ->options(function (Get $get): array {
                                $examId = $get('exam_id');
                                if (! $examId) {
                                    return [];
                                }

                                return ExamSchedule::where('exam_id', $examId)
                                    ->with(['class', 'subject'])
                                    ->get()
                                    ->mapWithKeys(fn (ExamSchedule $s): array => [
                                        $s->id => "{$s->class?->name} — {$s->subject?->name} ({$s->exam_date?->format('d/m')})",
                                    ])
                                    ->all();
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                if (! $state) {
                                    return;
                                }
                                $schedule = ExamSchedule::find($state);
                                if ($schedule) {
                                    $set('class_id', $schedule->class_id);
                                    $set('subject_id', $schedule->subject_id);
                                    $set('max_marks', $schedule->max_marks);
                                    $set('pass_marks', $schedule->pass_marks);
                                }
                            }),

                        Select::make('student_id')
                            ->relationship('student', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $r): string => "{$r->full_name} (Roll: {$r->roll_no})")
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->preload()
                            ->required(),

                        Hidden::make('class_id'),
                        Hidden::make('subject_id'),

                        TextInput::make('theory_marks')
                            ->label('Theory Marks')
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                'total_marks',
                                (float) ($get('theory_marks') ?? 0) + (float) ($get('practical_marks') ?? 0)
                            )),

                        TextInput::make('practical_marks')
                            ->label('Practical Marks')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set(
                                'total_marks',
                                (float) ($get('theory_marks') ?? 0) + (float) ($get('practical_marks') ?? 0)
                            )),

                        TextInput::make('total_marks')
                            ->label('Total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('max_marks')
                            ->numeric()
                            ->default(100),

                        TextInput::make('pass_marks')
                            ->numeric()
                            ->default(33),

                        TextEntry::make('auto_grade')
                            ->label('Auto Grade')
                            ->state(fn (Get $get): string => self::getGrade(
                                $get('total_marks'), $get('max_marks')
                            )),

                        Select::make('result')
                            ->options([
                                'pass' => '✅ Pass',
                                'fail' => '❌ Fail',
                                'absent' => '🚫 Absent',
                                'withheld' => '⏸️ Withheld',
                            ]),

                        TextInput::make('remark'),
                    ])->columns(3),
            ]);
    }

    // Grade calculator helper
    public static function getGrade($total, $max): string
    {
        if (! $total || ! $max) {
            return '—';
        }

        $p = ((float) $total / (float) $max) * 100;

        return match (true) {
            $p >= 90 => 'A+ (Outstanding)',
            $p >= 80 => 'A (Excellent)',
            $p >= 70 => 'B+ (Very Good)',
            $p >= 60 => 'B (Good)',
            $p >= 50 => 'C (Average)',
            $p >= 33 => 'D (Below Average)',
            default => 'F (Fail)',
        };
    }
}
