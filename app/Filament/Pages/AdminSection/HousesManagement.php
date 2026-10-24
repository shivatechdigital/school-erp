<?php

namespace App\Filament\Pages\AdminSection;

use App\Models\House;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;

class HousesManagement extends AdminSectionManagementPage
{
    protected static string $model = House::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Houses';

    protected static ?string $title = 'Houses';

    protected function recordLabel(): string
    {
        return 'House';
    }

    protected function recordFormComponents(): array
    {
        return [
            TextInput::make('name')->label('House Name')->required()->maxLength(255),
            ColorPicker::make('color')->label('House Color')->required(),
            Toggle::make('is_active')->default(true),
        ];
    }

    protected function recordTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('S.I.')->sortable(),
            ColorColumn::make('color')->label('House Color'),
            TextColumn::make('name')->label('House Name')->searchable()->sortable(),
            TextColumn::make('students_count')->label('Total Students')->counts('students')->alignCenter(),
        ];
    }
}
