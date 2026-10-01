<?php

namespace App\Filament\Resources\HostelRooms;

use App\Filament\Resources\HostelRooms\Pages\CreateHostelRoom;
use App\Filament\Resources\HostelRooms\Pages\EditHostelRoom;
use App\Filament\Resources\HostelRooms\Pages\ListHostelRooms;
use App\Filament\Resources\HostelRooms\Schemas\HostelRoomForm;
use App\Filament\Resources\HostelRooms\Tables\HostelRoomsTable;
use App\Models\HostelRoom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HostelRoomResource extends Resource
{
    protected static ?string $model = HostelRoom::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Hostel';

    protected static ?string $navigationLabel = 'Rooms';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return HostelRoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HostelRoomsTable::configure($table);
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
            'index' => ListHostelRooms::route('/'),
            'create' => CreateHostelRoom::route('/create'),
            'edit' => EditHostelRoom::route('/{record}/edit'),
        ];
    }
}
