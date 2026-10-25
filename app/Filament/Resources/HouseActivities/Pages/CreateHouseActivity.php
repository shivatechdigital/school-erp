<?php

namespace App\Filament\Resources\HouseActivities\Pages;

use App\Filament\Resources\HouseActivities\HouseActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHouseActivity extends CreateRecord
{
    protected static string $resource = HouseActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    /** Every active house participates by default; admin can reassign/skip via the In-charge screen. */
    protected function afterCreate(): void
    {
        $this->record->syncHouseAssignments();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
