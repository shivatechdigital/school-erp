<?php

namespace App\Filament\Resources\BookIssues\Pages;

use App\Filament\Resources\BookIssues\BookIssueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookIssues extends ListRecords
{
    protected static string $resource = BookIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
