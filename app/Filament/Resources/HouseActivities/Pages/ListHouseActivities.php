<?php

namespace App\Filament\Resources\HouseActivities\Pages;

use App\Filament\Resources\HouseActivities\HouseActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHouseActivities extends ListRecords
{
    protected static string $resource = HouseActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
