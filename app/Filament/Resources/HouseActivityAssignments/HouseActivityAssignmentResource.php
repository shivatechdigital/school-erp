<?php

namespace App\Filament\Resources\HouseActivityAssignments;

use App\Filament\Resources\HouseActivityAssignments\Pages\ListHouseActivityAssignments;
use App\Filament\Resources\HouseActivityAssignments\Tables\HouseActivityAssignmentsTable;
use App\Models\HouseActivityAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HouseActivityAssignmentResource extends Resource
{
    protected static ?string $model = HouseActivityAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Activities';

    protected static ?string $navigationLabel = 'House In-charges';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return HouseActivityAssignmentsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHouseActivityAssignments::route('/'),
        ];
    }
}
