<?php

namespace App\Filament\Resources\FeeHeads\Pages;

use App\Filament\Resources\FeeHeads\FeeHeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeeHead extends EditRecord
{
    protected static string $resource = FeeHeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
