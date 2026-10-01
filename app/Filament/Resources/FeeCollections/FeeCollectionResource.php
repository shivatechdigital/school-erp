<?php

namespace App\Filament\Resources\FeeCollections;

use App\Filament\Resources\FeeCollections\Pages\CreateFeeCollection;
use App\Filament\Resources\FeeCollections\Pages\ListFeeCollections;
use App\Filament\Resources\FeeCollections\Pages\ViewFeeCollection;
use App\Filament\Resources\FeeCollections\Schemas\FeeCollectionForm;
use App\Filament\Resources\FeeCollections\Tables\FeeCollectionsTable;
use App\Models\FeeCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeeCollectionResource extends Resource
{
    protected static ?string $model = FeeCollection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-rupee';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Fee Collection';

    public static function form(Schema $schema): Schema
    {
        return FeeCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeCollectionsTable::configure($table);
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
            'index' => ListFeeCollections::route('/'),
            'create' => CreateFeeCollection::route('/create'),
            'view' => ViewFeeCollection::route('/{record}'),
        ];
    }
}
