<?php

namespace App\Filament\Resources\ExamSchedules\Pages;

use App\Filament\Resources\ExamSchedules\ExamScheduleResource;
use App\Models\Exam;
use Filament\Resources\Pages\CreateRecord;

class CreateExamSchedule extends CreateRecord
{
    protected static string $resource = ExamScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // school_id is NOT NULL; super admin has no school, so take it from the exam.
        $data['school_id'] ??= Exam::query()->whereKey($data['exam_id'] ?? null)->value('school_id');

        return $data;
    }
}
