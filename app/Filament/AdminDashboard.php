<?php

namespace App\Filament;

use App\Filament\Pages\TeacherDashboard;
use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        if (auth()->user()?->user_type === 'teacher') {
            $this->redirect(TeacherDashboard::getUrl());
        }
    }

    public static function getNavigationItems(): array
    {
        if (auth()->user()?->user_type === 'teacher') {
            return [];
        }

        return parent::getNavigationItems();
    }
}