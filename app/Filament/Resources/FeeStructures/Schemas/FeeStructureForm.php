<?php

namespace App\Filament\Resources\FeeStructures\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FeeStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        // branch_id is NOT NULL; users without a fixed branch (topbar set to "All Branches") must pick one.
        $needsBranch = fn (): bool => ! auth()->user()?->branch_id;

        return $schema
            ->columns(1)
            ->components([
                Section::make('Fee Structure Setup')
                    ->schema([
                        Select::make('branch_id')
                            ->relationship(
                                'branch',
                                'name',
                                fn (Builder $query) => $query->when(
                                    auth()->user()?->school_id,
                                    fn (Builder $q, $schoolId) => $q->where('school_id', $schoolId)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->visible($needsBranch)
                            ->required($needsBranch),

                        Select::make('academic_year_id')
                            ->relationship('academicYear', 'name')
                            ->required()
                            ->label('Academic Year'),

                        Select::make('class_id')
                            ->relationship('class', 'name')
                            ->required()
                            ->label('Class'),

                        Select::make('fee_head_id')
                            ->relationship('feeHead', 'name')
                            ->required()
                            ->label('Fee Head'),

                        Select::make('category')
                            ->options([
                                'All' => 'All Categories',
                                'General' => 'General',
                                'OBC' => 'OBC',
                                'SC' => 'SC',
                                'ST' => 'ST',
                                'EWS' => 'EWS',
                            ])->default('All'),

                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->required()
                            ->placeholder('e.g., 2000'),

                        TextInput::make('fine_per_day')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0)
                            ->label('Late Fine (per day)'),

                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Toggle::make('is_active')->default(true),
                    ])->columns(2),
            ]);
    }
}
