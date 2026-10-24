<?php

namespace App\Filament\Resources\HouseActivityRegistrations;

use App\Filament\Resources\HouseActivityRegistrations\Pages\CreateHouseActivityRegistration;
use App\Filament\Resources\HouseActivityRegistrations\Pages\ListHouseActivityRegistrations;
use App\Filament\Resources\HouseActivityRegistrations\Schemas\HouseActivityRegistrationForm;
use App\Filament\Resources\HouseActivityRegistrations\Tables\HouseActivityRegistrationsTable;
use App\Models\HouseActivityRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HouseActivityRegistrationResource extends Resource
{
    protected static ?string $model = HouseActivityRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Activities';

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return HouseActivityRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HouseActivityRegistrationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHouseActivityRegistrations::route('/'),
            'create' => CreateHouseActivityRegistration::route('/create'),
        ];
    }
}
