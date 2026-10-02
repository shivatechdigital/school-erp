<?php

namespace App\Filament\Resources\Homework\Pages;

use App\Filament\Resources\Homework\HomeworkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomework extends EditRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['section_id'] = $data['section_ids'][0] ?? $this->record->section_id;
        unset($data['section_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->sections()->sync($this->form->getState()['section_ids'] ?? [$this->record->section_id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
