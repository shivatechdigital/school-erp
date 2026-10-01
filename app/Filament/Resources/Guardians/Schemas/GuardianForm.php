<?php

namespace App\Filament\Resources\Guardians\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GuardianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('school_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('name')
                    ->required(),
                Select::make('relation')
                    ->options(['father' => 'Father', 'mother' => 'Mother', 'guardian' => 'Guardian', 'other' => 'Other'])
                    ->default('father')
                    ->required(),
                Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                    ->default(null),
                DatePicker::make('date_of_birth'),
                TextInput::make('photo')
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('alternate_phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('whatsapp')
                    ->default(null),
                TextInput::make('occupation')
                    ->default(null),
                TextInput::make('company_name')
                    ->default(null),
                TextInput::make('annual_income')
                    ->numeric()
                    ->default(null),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->default(null),
                TextInput::make('state')
                    ->default(null),
                TextInput::make('pincode')
                    ->default(null),
                TextInput::make('aadhaar_no')
                    ->default(null),
                TextInput::make('pan_no')
                    ->default(null),
                Toggle::make('is_primary_contact')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
