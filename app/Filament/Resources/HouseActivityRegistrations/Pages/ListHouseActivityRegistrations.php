<?php

namespace App\Filament\Resources\HouseActivityRegistrations\Pages;

use App\Filament\Resources\HouseActivityRegistrations\HouseActivityRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHouseActivityRegistrations extends ListRecords
{
    protected static string $resource = HouseActivityRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Apply for Student'),
        ];
    }
}
