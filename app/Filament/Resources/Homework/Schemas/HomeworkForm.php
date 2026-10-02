<?php

namespace App\Filament\Resources\Homework\Schemas;

use App\Models\AcademicYear;
use App\Models\Homework;
use App\Models\SchoolClass;
use App\Models\Section as SchoolSection;
use App\Models\Subject;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HomeworkForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Class & Subject Selection')
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

                        Select::make('class_id')
                            ->label('Class')
                            ->options(fn (): array => SchoolClass::query()
                                ->when(auth()->user()?->user_type === 'teacher', fn ($query) => $query->whereHas(
                                    'subjects',
                                    fn ($subjects) => $subjects->where('class_subject.teacher_id', auth()->id())
                                ))
                                ->orderBy('sort_order')->pluck('name', 'id')->all())
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('section_ids', []);
                                $set('section_id', null);
                                $set('subject_id', null);
                            })
                            ->required(),

                        Select::make('section_ids')
                            ->label('Sections')
                            ->options(fn (Get $get): array => SchoolSection::query()
                                ->where('class_id', $get('class_id'))
                                ->where('is_active', true)
                                ->orderBy('name')->pluck('name', 'id')->all())
                            ->multiple()
                            ->searchable()
                            ->default(fn (?Homework $record): array => $record
                                ? ($record->sections->isNotEmpty() ? $record->sections->modelKeys() : [$record->section_id])
                                : [])
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?array $state) => $set('section_id', $state[0] ?? null))
                            ->required(),

                        Hidden::make('section_id')->required(),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->options(fn (Get $get): array => Subject::query()
                                ->whereHas('classes', function ($classes) use ($get): void {
                                    $classes->where('classes.id', $get('class_id'))
                                        ->when(auth()->user()?->user_type === 'teacher', fn ($query) => $query->where('class_subject.teacher_id', auth()->id()));
                                })
                                ->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                Section::make('Assignment Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title / Topic')
                            ->placeholder('e.g. Chapter 4 - Quadratic Equations Exercise 4.2')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('max_marks')
                            ->label('Maximum Marks')
                            ->numeric()
                            ->minValue(1)
                            ->default(100)
                            ->required(),

                        DatePicker::make('assigned_date')
                            ->label('Assigned Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Submission Due Date')
                            ->default(now()->addDays(2))
                            ->afterOrEqual('assigned_date')
                            ->required(),

                        Toggle::make('allow_late_submission')
                            ->label('Allow Late Submissions')
                            ->default(true),

                        RichEditor::make('description')
                            ->label('Instructions / Questions')
                            ->columnSpanFull(),

                        FileUpload::make('attachments')
                            ->label('Worksheets / Question Papers (PDF/Images)')
                            ->multiple()
                            ->disk('public')
                            ->directory('homework-docs')
                            ->maxFiles(5)
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
