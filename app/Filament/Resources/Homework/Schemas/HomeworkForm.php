<?php

namespace App\Filament\Resources\Homework\Schemas;

use App\Models\AcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                            ->relationship('schoolClass', 'name')
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('section_id', null))
                            ->required(),

                        Select::make('section_id')
                            ->label('Section')
                            ->relationship('section', 'name', fn (Builder $query, Get $get) => $query->where('class_id', $get('class_id')))
                            ->required(),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(4),

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
