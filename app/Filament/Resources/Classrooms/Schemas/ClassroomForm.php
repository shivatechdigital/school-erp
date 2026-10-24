<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Models\SchoolClass;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->columns(1)->components([
            FormSection::make('Class Room Details')->columns(2)->schema([
                Select::make('school_id')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdmin)
                    ->required($isSuperAdmin)
                    ->live(),
                Select::make('branch_id')
                    ->label('Branch (optional)')
                    ->relationship(
                        'branch',
                        'name',
                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                            ->when(
                                $get('school_id') ?? auth()->user()?->school_id,
                                fn (Builder $branches, int|string $schoolId): Builder => $branches->where('school_id', $schoolId),
                            ),
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => ! auth()->user()?->branch_id),
                TextInput::make('room_number')
                    ->label('Room Number / Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('floor')
                    ->placeholder('Ground, First Floor, Basement')
                    ->required()
                    ->maxLength(80),
                TextInput::make('capacity')
                    ->numeric()
                    ->minValue(1)
                    ->default(30)
                    ->required(),
                Select::make('class_id')
                    ->label('Class')
                    ->options(fn (Get $get): array => SchoolClass::query()
                        ->when(
                            $get('school_id') ?? auth()->user()?->school_id,
                            fn (Builder $classes, int|string $schoolId): Builder => $classes->where('school_id', $schoolId),
                        )
                        ->orderBy('sort_order')
                        ->pluck('name', 'id')
                        ->all())
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(fn (callable $set) => $set('section_id', null)),
                Select::make('section_id')
                    ->label('Section')
                    ->options(function (Get $get): array {
                        if (! $get('class_id')) {
                            return [];
                        }

                        return Section::query()
                            ->where('class_id', $get('class_id'))
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->placeholder('Not assigned to a section yet'),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }
}
