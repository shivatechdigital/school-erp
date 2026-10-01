<?php

namespace App\Filament\Resources\StudentTransports\Pages;

use App\Filament\Resources\StudentTransports\StudentTransportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentTransport extends EditRecord
{
    protected static string $resource = StudentTransportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
