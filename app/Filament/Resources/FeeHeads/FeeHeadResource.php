<?php

namespace App\Filament\Resources\FeeHeads;

use App\Filament\Resources\FeeHeads\Pages\CreateFeeHead;
use App\Filament\Resources\FeeHeads\Pages\EditFeeHead;
use App\Filament\Resources\FeeHeads\Pages\ListFeeHeads;
use App\Filament\Resources\FeeHeads\Schemas\FeeHeadForm;
use App\Filament\Resources\FeeHeads\Tables\FeeHeadsTable;
use App\Models\FeeHead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeeHeadResource extends Resource
{
    use \App\Filament\Resources\Concerns\HidesResourcesFromTeachers;

    protected static ?string $model = FeeHead::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Fee Heads';

    public static function form(Schema $schema): Schema
    {
        return FeeHeadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeHeadsTable::configure($table);
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
            'index' => ListFeeHeads::route('/'),
            'create' => CreateFeeHead::route('/create'),
            'edit' => EditFeeHead::route('/{record}/edit'),
        ];
    }
}
