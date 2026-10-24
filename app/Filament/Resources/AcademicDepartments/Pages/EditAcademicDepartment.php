<?php

namespace App\Filament\Resources\AcademicDepartments\Pages;

use App\Filament\Resources\AcademicDepartments\AcademicDepartmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicDepartment extends EditRecord
{
    protected static string $resource = AcademicDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
