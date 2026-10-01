<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_attendance')
                ->label('Mark Attendance')
                ->icon('heroicon-o-check-badge')
                ->url(AttendanceResource::getUrl('bulk')),
        ];
    }
}
