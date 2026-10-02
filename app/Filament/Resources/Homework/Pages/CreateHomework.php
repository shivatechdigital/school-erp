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
        $data['section_id'] = $data['section_ids'][0] ?? null;
        unset($data['section_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->sections()->sync($this->form->getState()['section_ids'] ?? [$this->record->section_id]);
    }
}
