<?php

namespace App\Filament\Resources\HostelRooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class HostelRoomForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Room Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        Select::make('hostel_id')
                            ->label('Hostel')
                            ->relationship('hostel', 'name', fn (Builder $query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),

                        TextInput::make('room_number')
                            ->label('Room Number')
                            ->placeholder('e.g. 101, A-202')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('hostel_id', $get('hostel_id'))),

                        Select::make('room_type')
                            ->options([
                                'single' => 'Single Seater',
                                'double' => 'Double Seater',
                                'triple' => 'Triple Seater',
                                'dormitory' => 'Dormitory (4+ Beds)',
                                'ac' => 'AC Room',
                                'non_ac' => 'Non-AC Room',
                            ])
                            ->default('double')
                            ->required(),

                        TextInput::make('bed_capacity')
                            ->label('Bed Capacity')
                            ->numeric()
                            ->minValue(1)
                            ->default(2)
                            ->required(),

                        TextInput::make('cost_per_bed')
                            ->label('Monthly Rent / Bed (₹)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0.00)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Operational / Ready')
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
