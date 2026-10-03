<?php

namespace App\Filament\Resources\StudentTransports\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StudentTransportForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Student Allocation')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => "{$record->full_name} (Adm: {$record->admission_no})")
                            ->searchable(['first_name', 'last_name', 'admission_no'])
                            ->preload()
                            ->required(),

                        Select::make('route_id')
                            ->label('Route')
                            ->relationship('route', 'title', fn (Builder $query) => $query->where('is_active', true))
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('route_stop_id', null))
                            ->required(),

                        Select::make('route_stop_id')
                            ->label('Pickup / Drop Stop')
                            ->relationship('stop', 'stop_name', fn (Builder $query, Get $get) => $query->where('route_id', $get('route_id')))
                            ->required(),

                        Select::make('trip_type')
                            ->label('Trip Type')
                            ->options([
                                'both' => 'Both Ways (Pickup & Drop)',
                                'pickup_only' => 'Pickup Only',
                                'drop_only' => 'Drop Only',
                            ])
                            ->default('both')
                            ->required(),

                        DatePicker::make('start_date')
                            ->label('Service Start Date')
                            ->default(now())
                            ->required(),

                        DatePicker::make('end_date')
                            ->label('Service End Date (Optional)')
                            ->afterOrEqual('start_date'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
