<?php

namespace App\Filament\Resources\FeeHeads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeeHeadForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema
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
                        ->placeholder('e.g., Tuition Fee, Transport Fee'),
                    TextInput::make('code')
                        ->placeholder('e.g., TF, TRF'),
                    Select::make('type')
                        ->options([
                            'mandatory' => 'Mandatory',
                            'optional' => 'Optional',
                        ])->default('mandatory'),
                    Select::make('frequency')
                        ->options([
                            'one_time' => 'One Time',
                            'monthly' => 'Monthly',
                            'quarterly' => 'Quarterly',
                            'half_yearly' => 'Half Yearly',
                            'yearly' => 'Yearly',
                        ])->default('monthly'),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
            ]);
    }
}
