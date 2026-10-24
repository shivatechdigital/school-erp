<?php

namespace App\Filament\Resources\DepartmentRequests\Pages;

use App\Filament\Resources\DepartmentRequests\DepartmentRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartmentRequests extends ListRecords
{
    protected static string $resource = DepartmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
