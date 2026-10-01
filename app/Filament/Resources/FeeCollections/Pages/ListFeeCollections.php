<?php

namespace App\Filament\Resources\FeeCollections\Pages;

use App\Filament\Resources\FeeCollections\FeeCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeeCollections extends ListRecords
{
    protected static string $resource = FeeCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
