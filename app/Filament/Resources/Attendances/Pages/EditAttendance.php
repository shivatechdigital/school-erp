<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\AttendanceAccessGrant;
use App\Models\StudentAttendanceAudit;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    private ?string $oldStatus = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldStatus = $this->record->status;
        $data['marked_by'] = auth()->id();
        $data['marked_at'] = now();

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->oldStatus === $this->record->status) {
            return;
        }

        $grant = null;
        if (auth()->user()?->user_type === 'teacher' && $this->record->section?->class_teacher_id !== auth()->id()) {
            $grant = AttendanceAccessGrant::query()
                ->where('section_id', $this->record->section_id)
                ->where('teacher_id', auth()->id())
                ->whereNull('revoked_at')
                ->where('valid_from', '<=', now())
                ->where('valid_until', '>', now())
                ->latest('valid_until')
                ->first();
        }

        StudentAttendanceAudit::query()->create([
            'student_attendance_id' => $this->record->id,
            'changed_by' => auth()->id(),
            'access_grant_id' => $grant?->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->record->status,
            'changed_at' => now(),
        ]);
    }
}
