<?php

namespace App\Filament\Resources\AcademicDepartments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicDepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->placeholder('e.g., Science'),
                    TextInput::make('code')
                        ->placeholder('e.g., SCI'),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
            ]);
    }
}
