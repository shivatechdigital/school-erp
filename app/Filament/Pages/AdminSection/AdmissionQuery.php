<?php

namespace App\Filament\Pages\AdminSection;

use Filament\Pages\Page;

class AdmissionQuery extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Admin Section';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Admission Query';

    protected static ?string $title = 'Admission Query';

    protected string $view = 'filament.pages.admin-section.admission-query';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->user_type, ['super_admin', 'school_admin', 'branch_admin'], true);
    }
}
