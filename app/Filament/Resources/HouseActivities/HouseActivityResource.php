<?php

namespace App\Filament\Resources\HouseActivities;

use App\Filament\Resources\HouseActivities\Pages\CreateHouseActivity;
use App\Filament\Resources\HouseActivities\Pages\EditHouseActivity;
use App\Filament\Resources\HouseActivities\Pages\ListHouseActivities;
use App\Filament\Resources\HouseActivities\Schemas\HouseActivityForm;
use App\Filament\Resources\HouseActivities\Tables\HouseActivitiesTable;
use App\Models\HouseActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HouseActivityResource extends Resource
{
    protected static ?string $model = HouseActivity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'Activities';

    protected static ?string $navigationLabel = 'Activities';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return HouseActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HouseActivitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHouseActivities::route('/'),
            'create' => CreateHouseActivity::route('/create'),
            'edit' => EditHouseActivity::route('/{record}/edit'),
        ];
    }
}
