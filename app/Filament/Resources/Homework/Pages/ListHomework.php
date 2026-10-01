<?php

namespace App\Filament\Resources\Homework\Pages;

use App\Filament\Resources\Homework\HomeworkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomework extends ListRecords
{
    protected static string $resource = HomeworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
