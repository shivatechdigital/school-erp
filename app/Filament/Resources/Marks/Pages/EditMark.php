<?php

namespace App\Filament\Resources\Marks\Pages;

use App\Filament\Resources\Marks\MarkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMark extends EditRecord
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
