<?php

namespace App\Filament\Resources\Homework\RelationManagers;

use App\Models\HomeworkSubmission;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('marks_obtained')
                    ->label('Marks Obtained')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn (): int => $this->getOwnerRecord()->max_marks)
                    ->required(),

                Select::make('status')
                    ->options([
                        'evaluated' => 'Evaluated / Approved',
                        'resubmit_required' => 'Needs Resubmission',
                    ])
                    ->default('evaluated')
                    ->required(),

                Textarea::make('teacher_feedback')
                    ->label('Teacher Feedback / Remarks')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (HomeworkSubmission $record): string => (string) $record->student?->full_name)
            ->columns([
                TextColumn::make('student.admission_no')
                    ->label('Adm No.')
                    ->searchable(),

                TextColumn::make('student.first_name')
                    ->label('Student Name')
                    ->formatStateUsing(fn (HomeworkSubmission $record): string => (string) $record->student?->full_name)
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold'),

                TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'late' => 'warning',
                        'evaluated' => 'success',
                        'resubmit_required' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('marks_obtained')
                    ->label('Marks')
                    ->getStateUsing(fn (HomeworkSubmission $record): string => $record->marks_obtained !== null
                        ? "{$record->marks_obtained} / {$this->getOwnerRecord()->max_marks}"
                        : 'Pending')
                    ->alignCenter(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Evaluate / Grade')
                    ->icon('heroicon-m-check-badge')
                    ->mutateDataUsing(function (array $data): array {
                        $data['evaluated_by'] = auth()->id();
                        $data['evaluated_at'] = now();

                        return $data;
                    }),
            ]);
    }
}
