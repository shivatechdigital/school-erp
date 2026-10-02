<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AcademicYearResource extends \Filament\Resources\Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = AcademicYear::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->components([
            Section::make('Academic Year')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->maxLength(255),
                DatePicker::make('start_date')->required()->native(false),
                DatePicker::make('end_date')->required()->native(false)->after('start_date'),
                Toggle::make('is_current')->label('Current Academic Year'),
                Select::make('status')->options(['active' => 'Active', 'closed' => 'Closed'])->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('school.name')->label('School')->searchable(),
            TextColumn::make('name')->weight('bold')->sortable(),
            TextColumn::make('code')->badge(),
            TextColumn::make('start_date')->date(),
            TextColumn::make('end_date')->date(),
            IconColumn::make('is_current')->boolean()->label('Current'),
            TextColumn::make('status')->badge(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}