<?php

namespace App\Filament\Resources\HostelAllocations\Pages;

use App\Filament\Resources\HostelAllocations\HostelAllocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHostelAllocations extends ListRecords
{
    protected static string $resource = HostelAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
