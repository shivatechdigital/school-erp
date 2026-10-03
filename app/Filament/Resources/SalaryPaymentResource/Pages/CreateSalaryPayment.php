<?php

namespace App\Filament\Resources\SalaryPaymentResource\Pages;

use App\Filament\Resources\SalaryPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalaryPayment extends CreateRecord
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $staff = \App\Models\User::query()->findOrFail($data['user_id']);
        $data['school_id'] = $staff->school_id;
        $data['branch_id'] = $staff->branch_id;

        return $data;
    }
}
