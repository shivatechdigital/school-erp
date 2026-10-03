<?php

namespace App\Filament\Resources\Routes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RouteForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Route Information')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('title')
                            ->label('Route Name')
                            ->placeholder('e.g. Route 1 - City Center to Campus')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('vehicle_id')
                            ->label('Assigned Vehicle')
                            ->relationship('vehicle', 'vehicle_number', fn (Builder $query) => $query->where('is_active', true))
                            ->searchable()
                            ->preload(),

                        TextInput::make('start_point')
                            ->label('Start Point')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('end_point')
                            ->label('Destination Point')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active Route')
                            ->default(true),
                    ])->columns(3),

                Section::make('Stops & Timing')
                    ->schema([
                        Repeater::make('stops')
                            ->relationship('stops')
                            ->orderColumn('stop_order')
                            ->schema([
                                TextInput::make('stop_name')
                                    ->label('Stop Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TimePicker::make('pickup_time')
                                    ->label('Pickup Time')
                                    ->seconds(false)
                                    ->required(),

                                TimePicker::make('drop_time')
                                    ->label('Drop Time')
                                    ->seconds(false)
                                    ->required(),

                                TextInput::make('monthly_fare')
                                    ->label('Monthly Fare (₹)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₹')
                                    ->default(0.00)
                                    ->required(),
                            ])
                            // Super admin has no auto school_id, so inherit it from the parent route.
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data, Get $get): array => $data + ['school_id' => $get('school_id')])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Add New Stop'),
                    ]),
            ]);
    }
}
