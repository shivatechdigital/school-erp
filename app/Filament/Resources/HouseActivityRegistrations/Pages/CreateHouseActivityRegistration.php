<?php

namespace App\Filament\Resources\HouseActivityRegistrations\Pages;

use App\Filament\Resources\HouseActivityRegistrations\HouseActivityRegistrationResource;
use App\Models\HouseActivity;
use App\Models\HouseActivityAssignment;
use App\Models\HouseActivityRegistration;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateHouseActivityRegistration extends CreateRecord
{
    protected static string $resource = HouseActivityRegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $activity = HouseActivity::query()->find($data['house_activity_id']);
        $student = Student::query()->find($data['student_id']);

        if ($activity?->status !== 'registration_open') {
            Notification::make()->danger()->title('Registration is not open for this activity')->send();
            $this->halt();
        }

        if (! $student?->house_id) {
            Notification::make()->danger()->title('This student has no house assigned')->send();
            $this->halt();
        }

        if (HouseActivityRegistration::query()->where('house_activity_id', $data['house_activity_id'])->where('student_id', $data['student_id'])->exists()) {
            Notification::make()->danger()->title('This student has already applied for this activity')->send();
            $this->halt();
        }

        $data['house_id'] = $student->house_id;
        $data['applied_by'] = auth()->id();
        $data['status'] = 'applied';

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $incharge = HouseActivityAssignment::query()
            ->where('house_activity_id', $record->house_activity_id)
            ->where('house_id', $record->house_id)
            ->whereNotNull('incharge_user_id')
            ->with('incharge')
            ->first()
            ?->incharge;

        if ($incharge) {
            Notification::make()
                ->title('New activity registration')
                ->body($record->student->full_name.' applied for '.$record->activity->name)
                ->sendToDatabase($incharge);
        }
    }
}
