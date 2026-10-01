<?php

namespace App\Filament\Resources\Hostels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HostelForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->components([
                Section::make('Hostel Details')
                    ->schema([
                        Select::make('school_id')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible($isSuperAdmin)
                            ->required($isSuperAdmin),

                        TextInput::make('name')
                            ->label('Hostel / Block Name')
                            ->placeholder('e.g. Block A - Senior Boys')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'boys' => 'Boys Hostel',
                                'girls' => 'Girls Hostel',
                                'co_ed' => 'Co-Ed Hostel',
                            ])
                            ->required(),

                        TextInput::make('warden_name')
                            ->label('Warden Name')
                            ->maxLength(255),

                        TextInput::make('warden_phone')
                            ->label('Warden Phone')
                            ->tel()
                            ->maxLength(20),

                        Textarea::make('address')
                            ->label('Location / Address')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
