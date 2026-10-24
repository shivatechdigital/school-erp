<?php

namespace App\Filament\Resources\HouseActivities\Pages;

use App\Filament\Resources\HouseActivities\HouseActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHouseActivity extends EditRecord
{
    protected static string $resource = HouseActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
