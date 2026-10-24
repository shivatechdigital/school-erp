<?php

namespace App\Filament\Resources\HouseActivityRegistrations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class HouseActivityRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('house_activity_id')
                ->label('Activity')
                ->relationship(
                    'activity',
                    'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query
                        ->where('school_id', auth()->user()?->school_id)
                        ->where('status', 'registration_open'),
                )
                ->searchable()
                ->preload()
                ->required(),
            Select::make('student_id')
                ->label('Student')
                ->relationship(
                    'student',
                    'full_name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query
                        ->where('school_id', auth()->user()?->school_id)
                        ->where('status', 'active'),
                )
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }
}
