<?php

namespace App\Filament\Resources\FeeStructures;

use App\Filament\Resources\FeeStructures\Pages\CreateFeeStructure;
use App\Filament\Resources\FeeStructures\Pages\EditFeeStructure;
use App\Filament\Resources\FeeStructures\Pages\ListFeeStructures;
use App\Filament\Resources\FeeStructures\Schemas\FeeStructureForm;
use App\Filament\Resources\FeeStructures\Tables\FeeStructuresTable;
use App\Models\FeeStructure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeeStructureResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = FeeStructure::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-rupee';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Fee Structure';

    public static function form(Schema $schema): Schema
    {
        return FeeStructureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeStructuresTable::configure($table);
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
            'index' => ListFeeStructures::route('/'),
            'create' => CreateFeeStructure::route('/create'),
            'edit' => EditFeeStructure::route('/{record}/edit'),
        ];
    }
}
