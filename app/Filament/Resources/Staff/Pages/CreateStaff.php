<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user->user_type !== 'super_admin') {
            $data['school_id'] = $user->school_id;
            $data['branch_id'] = $user->branch_id;
        }

        return $data;
    }
}
