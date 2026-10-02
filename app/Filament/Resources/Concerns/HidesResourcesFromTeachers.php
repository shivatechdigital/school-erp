<?php

namespace App\Filament\Resources\Concerns;

trait HidesResourcesFromTeachers
{
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->user_type !== 'teacher';
    }
}