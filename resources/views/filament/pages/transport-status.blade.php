<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Active Vehicles</div>
                <div class="text-2xl font-bold">{{ $totalVehicles }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Active Routes</div>
                <div class="text-2xl font-bold">{{ $totalRoutes }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Students Using Transport</div>
                <div class="text-2xl font-bold">{{ $totalStudents }}</div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Routes" icon="heroicon-o-map">
            @forelse ($routes as $route)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                    <div>
                        <div class="font-medium">{{ $route->title }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $route->start_point }} &rarr; {{ $route->end_point }} &middot; Vehicle: {{ $route->vehicle?->vehicle_number ?? 'Unassigned' }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-filament::badge color="gray">{{ $route->stops_count }} stops</x-filament::badge>
                        <x-filament::badge color="primary">{{ $route->students_count }} students</x-filament::badge>
                    </div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-map" heading="No active routes" description="Transport routes will appear here once set up." compact />
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
