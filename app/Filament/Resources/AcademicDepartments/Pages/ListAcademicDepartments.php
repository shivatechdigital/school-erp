<?php

namespace App\Filament\Resources\AcademicDepartments\Pages;

use App\Filament\Resources\AcademicDepartments\AcademicDepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicDepartments extends ListRecords
{
    protected static string $resource = AcademicDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
