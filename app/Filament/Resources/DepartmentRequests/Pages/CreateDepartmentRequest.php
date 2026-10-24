<?php

namespace App\Filament\Resources\DepartmentRequests\Pages;

use App\Filament\Resources\DepartmentRequests\DepartmentRequestResource;
use App\Models\Department;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartmentRequest extends CreateRecord
{
    protected static string $resource = DepartmentRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $data['raised_by'] = $user?->id;

        $department = Department::query()->find($data['department_id'] ?? null);
        $isSelfHead = $department?->isHeadedBy($user?->id) ?? false;

        $data['status'] = ($department?->head_user_id && ! $isSelfHead) ? 'pending_head' : 'pending_admin';

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $recipients = $record->status === 'pending_head'
            ? $record->headRecipients()
            : $record->adminRecipients();

        foreach ($recipients as $recipient) {
            Notification::make()
                ->title('New department request')
                ->body($record->title)
                ->sendToDatabase($recipient);
        }
    }
}
