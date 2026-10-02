<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Models\SchoolClass;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SchoolClassResource extends \Filament\Resources\Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = SchoolClass::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Classes';

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->components([
            Section::make('Class Details')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->maxLength(255),
                Select::make('group')->options([
                    'pre_primary' => 'Pre-Primary', 'primary' => 'Primary', 'middle' => 'Middle',
                    'secondary' => 'Secondary', 'senior_secondary' => 'Senior Secondary',
                ])->required(),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
                Toggle::make('has_sections')->default(true),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('school.name')->label('School')->searchable(),
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('code')->badge(),
            TextColumn::make('group')->badge(),
            TextColumn::make('sort_order')->sortable(),
            IconColumn::make('has_sections')->boolean(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('students_count')->counts('students')->label('Students'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}