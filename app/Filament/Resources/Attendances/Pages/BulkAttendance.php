<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\Page;

class BulkAttendance extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.resources.attendances.pages.bulk-attendance';
}
