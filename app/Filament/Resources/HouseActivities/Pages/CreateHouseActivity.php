<?php

namespace App\Filament\Resources\HouseActivities\Pages;

use App\Filament\Resources\HouseActivities\HouseActivityResource;
use App\Models\House;
use App\Models\HouseActivityAssignment;
use Filament\Resources\Pages\CreateRecord;

class CreateHouseActivity extends CreateRecord
{
    protected static string $resource = HouseActivityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    /** Every active house participates by default; admin can reassign/skip via the In-charge screen. */
    protected function afterCreate(): void
    {
        $activity = $this->record;

        House::query()
            ->where('school_id', $activity->school_id)
            ->where('is_active', true)
            ->when($activity->branch_id, fn ($query) => $query->where(function ($query) use ($activity): void {
                $query->whereNull('branch_id')->orWhere('branch_id', $activity->branch_id);
            }))
            ->get()
            ->each(fn (House $house) => HouseActivityAssignment::query()->create([
                'house_activity_id' => $activity->id,
                'house_id' => $house->id,
            ]));
    }
}
