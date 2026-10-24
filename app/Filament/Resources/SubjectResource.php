<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\HidesResourcesFromTeachers;
use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
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

class SubjectResource extends \Filament\Resources\Resource
{
    use HidesResourcesFromTeachers;

    protected static ?string $model = Subject::class;

    protected static string|UnitEnum|null $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->columns(1)->components([
            Section::make('Subject Details')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->maxLength(255),
                Select::make('academic_department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Common (no department)'),
                Select::make('type')->options([
                    'theory' => 'Theory', 'practical' => 'Practical', 'both' => 'Theory + Practical',
                ])->required(),
                Toggle::make('is_optional'),
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
            TextColumn::make('department.name')->label('Department')->badge()->color('info')->placeholder('Common'),
            TextColumn::make('type')->badge(),
            IconColumn::make('is_optional')->boolean()->label('Optional'),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
