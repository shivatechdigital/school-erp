<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabResource\Pages;
use App\Models\Lab;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LabResource extends Resource
{
    use Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = Lab::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Labs';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('type')
                ->options([
                    'biology' => 'Biology Lab',
                    'chemistry' => 'Chemistry Lab',
                    'physics' => 'Physics Lab',
                    'mathematics' => 'Mathematics Lab',
                    'computer' => 'Computer Lab',
                    'language' => 'Language Lab',
                    'other' => 'Other',
                ])
                ->required(),
            TextInput::make('room_no')->label('Room No.')->maxLength(255),
            TextInput::make('capacity')->numeric()->minValue(1)->default(30)->required(),
            Select::make('incharge_id')
                ->label('Lab Incharge')
                ->relationship('incharge', 'name')
                ->searchable(),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('room_no')->label('Room'),
                TextColumn::make('capacity')->alignCenter(),
                TextColumn::make('incharge.name')->label('Incharge')->placeholder('—'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabs::route('/'),
            'create' => Pages\CreateLab::route('/create'),
            'edit' => Pages\EditLab::route('/{record}/edit'),
        ];
    }
}
