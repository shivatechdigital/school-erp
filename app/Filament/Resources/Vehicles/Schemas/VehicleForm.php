<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Vehicle Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('vehicle_number')
                            ->label('Vehicle Reg. No.')
                            ->placeholder('e.g. DL-01-AB-1234')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('vehicle_model')
                            ->label('Model / Type')
                            ->placeholder('e.g. Tata Starbus (40 Seater)')
                            ->maxLength(100),

                        TextInput::make('capacity')
                            ->label('Seating Capacity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('gps_device_id')
                            ->label('GPS Device ID (Optional)')
                            ->placeholder('e.g. GPS-TR-9902')
                            ->maxLength(100),
                    ])->columns(2),

                Section::make('Driver & Crew Details')
                    ->schema([
                        TextInput::make('driver_name')
                            ->label('Driver Name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('driver_phone')
                            ->label('Driver Phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        TextInput::make('driver_license')
                            ->label('Driver License No.')
                            ->maxLength(50),

                        TextInput::make('helper_name')
                            ->label('Helper / Conductor Name')
                            ->maxLength(100),

                        TextInput::make('helper_phone')
                            ->label('Helper Phone')
                            ->tel()
                            ->maxLength(20),

                        Toggle::make('is_active')
                            ->label('Operational / Active')
                            ->default(true),
                    ])->columns(3),
            ]);
    }
}
