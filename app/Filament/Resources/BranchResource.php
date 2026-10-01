<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BranchResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $isSuperAdmin = fn (): bool => auth()->user()?->user_type === 'super_admin';

        return $schema->components([
            Section::make('Branch Details')->columns(2)->schema([
                Select::make('school_id')->relationship('school', 'name')->searchable()->preload()
                    ->visible($isSuperAdmin)->required($isSuperAdmin),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
                Toggle::make('is_main_branch'),
                TextInput::make('phone')->tel()->maxLength(255),
                TextInput::make('email')->email()->maxLength(255),
                TextInput::make('principal_name')->maxLength(255),
                TextInput::make('principal_phone')->tel()->maxLength(255),
                Textarea::make('address')->rows(2)->columnSpanFull(),
                TextInput::make('city')->maxLength(255),
                TextInput::make('state')->maxLength(255),
                TextInput::make('pincode')->maxLength(6),
                Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('school.name')->label('School')->searchable(),
            TextColumn::make('code')->badge()->searchable(),
            IconColumn::make('is_main_branch')->boolean()->label('Main'),
            TextColumn::make('principal_name'),
            TextColumn::make('phone'),
            TextColumn::make('city'),
            TextColumn::make('status')->badge(),
            TextColumn::make('students_count')->counts('students')->label('Students'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin'], true);
    }
}