<?php

namespace App\Filament\Resources\LabResource\Pages;

use App\Filament\Resources\LabResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLab extends CreateRecord
{
    protected static string $resource = LabResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['school_id'] = auth()->user()->school_id;
        $data['branch_id'] = auth()->user()->branch_id;

        return $data;
    }
}
