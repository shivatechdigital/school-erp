<?php

namespace App\Filament\Resources\Homework\Pages;

use App\Filament\Resources\Homework\HomeworkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomework extends CreateRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['teacher_id'] = auth()->id();

        return $data;
    }
}
