<?php

namespace App\Filament;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }
}