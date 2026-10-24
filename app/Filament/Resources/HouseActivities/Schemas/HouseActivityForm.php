<?php

namespace App\Filament\Resources\HouseActivities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HouseActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->columns(1)->components([
            Section::make('Activity Details')->columns(2)->schema([
                Select::make('school_id')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdmin)
                    ->required($isSuperAdmin),
                TextInput::make('name')->required()->maxLength(255)->placeholder('e.g., Inter-House Cricket'),
                Select::make('category')
                    ->options([
                        'sports' => 'Sports',
                        'cultural' => 'Cultural',
                        'debate' => 'Debate / Quiz',
                        'other' => 'Other',
                    ])
                    ->required(),
                TextInput::make('max_participants_per_house')
                    ->label('Max Participants per House')
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('Leave blank for unlimited'),
                DatePicker::make('registration_start_date')->label('Registration Start')->native(false),
                DatePicker::make('registration_end_date')->label('Registration End')->native(false)->afterOrEqual('registration_start_date'),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }
}
