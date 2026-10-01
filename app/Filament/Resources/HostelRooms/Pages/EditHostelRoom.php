<?php

namespace App\Filament\Resources\HostelRooms\Pages;

use App\Filament\Resources\HostelRooms\HostelRoomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHostelRoom extends EditRecord
{
    protected static string $resource = HostelRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
