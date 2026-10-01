<?php

namespace App\Filament\Resources\StudentTransports\Pages;

use App\Filament\Resources\StudentTransports\StudentTransportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentTransports extends ListRecords
{
    protected static string $resource = StudentTransportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
