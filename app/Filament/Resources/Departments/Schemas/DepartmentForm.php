<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
            ->columns(1)
            ->components([
                Section::make()->schema([
                    Select::make('school_id')
                        ->relationship('school', 'name')
                        ->searchable()
                        ->preload()
                        ->visible($isSuperAdmin)
                        ->required($isSuperAdmin),
                    TextInput::make('name')
                        ->required()
                        ->placeholder('e.g., Mathematics'),
                    TextInput::make('code')
                        ->placeholder('e.g., MATH'),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
            ]);
    }
}
