<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHouse extends CreateRecord
{
    protected static string $resource = HouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id'] = auth()->user()->school_id;
        $data['branch_id'] = auth()->user()->branch_id;

        return $data;
    }
}
