<?php

namespace App\Filament\Resources\HostelRooms\Pages;

use App\Filament\Resources\HostelRooms\HostelRoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHostelRooms extends ListRecords
{
    protected static string $resource = HostelRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
