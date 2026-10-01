<?php

namespace App\Filament\Resources\Marks;

use App\Filament\Resources\Marks\Pages\CreateMark;
use App\Filament\Resources\Marks\Pages\EditMark;
use App\Filament\Resources\Marks\Pages\ListMarks;
use App\Filament\Resources\Marks\Schemas\MarkForm;
use App\Filament\Resources\Marks\Tables\MarksTable;
use App\Models\Mark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class MarkResource extends Resource
{
    protected static ?string $model = Mark::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|UnitEnum|null $navigationGroup = 'Examination';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Marks Entry';

    public static function form(Schema $schema): Schema
    {
        return MarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarks::route('/'),
            'create' => CreateMark::route('/create'),
            'edit' => EditMark::route('/{record}/edit'),
        ];
    }
}
