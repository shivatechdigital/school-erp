<?php

namespace App\Filament\Pages;

use App\Models\Route;
use App\Models\StudentTransport;
use App\Models\Vehicle;
use Filament\Pages\Page;

class TransportStatus extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Transport';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Transport Status';

    protected static ?string $title = 'Transport Status';

    protected string $view = 'filament.pages.transport-status';

    public static function canAccess(): bool
    {
        return auth()->user()?->user_type !== 'teacher';
    }

    protected function getViewData(): array
    {
        $routes = Route::query()
            ->with(['vehicle'])
            ->withCount(['stops', 'studentTransports as students_count' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->get();

        return [
            'totalVehicles' => Vehicle::query()->where('is_active', true)->count(),
            'totalRoutes' => $routes->count(),
            'totalStudents' => StudentTransport::query()->where('is_active', true)->count(),
            'routes' => $routes,
        ];
    }
}
