<?php

namespace App\Filament\Resources\BookIssues\Pages;

use App\Filament\Resources\BookIssues\BookIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookIssue extends EditRecord
{
    protected static string $resource = BookIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
