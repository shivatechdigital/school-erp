<?php

namespace App\Filament\Resources\HostelAllocations\Pages;

use App\Filament\Resources\HostelAllocations\HostelAllocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHostelAllocation extends EditRecord
{
    protected static string $resource = HostelAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
