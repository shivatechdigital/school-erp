<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->placeholder('e.g., Mathematics'),
                    TextInput::make('code')
                        ->placeholder('e.g., MATH'),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Select::make('head_user_id')
                        ->label('Department Head')
                        ->relationship(
                            'head',
                            'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->when(
                                    auth()->user()?->school_id,
                                    fn (Builder $users, int|string $schoolId): Builder => $users->where('school_id', $schoolId),
                                ),
                        )
                        ->searchable()
                        ->preload(),
                    Select::make('sub_head_user_id')
                        ->label('Sub Head (optional)')
                        ->relationship(
                            'subHead',
                            'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->when(
                                    auth()->user()?->school_id,
                                    fn (Builder $users, int|string $schoolId): Builder => $users->where('school_id', $schoolId),
                                ),
                        )
                        ->searchable()
                        ->preload(),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
            ]);
    }
}
