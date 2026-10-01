<?php

namespace App\Filament\Resources\FeeCollections\Pages;

use App\Filament\Resources\FeeCollections\FeeCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeeCollection extends EditRecord
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
