<?php

namespace App\Filament\Resources\HouseActivities\Pages;

use App\Filament\Resources\HouseActivities\HouseActivityResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHouseActivity extends EditRecord
{
    protected static string $resource = HouseActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncHouseAssignments')
                ->label('Sync House Assignments')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $created = $this->record->syncHouseAssignments();

                    $created > 0
                        ? Notification::make()->success()->title("{$created} house(s) added to this activity")->send()
                        : Notification::make()->success()->title('Every active house is already assigned')->send();
                }),
            DeleteAction::make(),
        ];
    }
}
