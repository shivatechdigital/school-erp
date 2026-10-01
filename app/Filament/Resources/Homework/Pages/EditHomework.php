<?php

namespace App\Filament\Resources\Homework\Pages;

use App\Filament\Resources\Homework\HomeworkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomework extends EditRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
