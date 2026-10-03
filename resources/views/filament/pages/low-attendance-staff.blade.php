<x-filament-panels::page>
    <x-filament::section heading="Staff with attendance below {{ \App\Filament\Pages\LowAttendanceStaff::THRESHOLD }}% this year" icon="heroicon-o-exclamation-triangle">
        @forelse ($rows as $row)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div>
                    <div class="font-medium">{{ $row['user']->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ ucwords(str_replace('_', ' ', $row['user']->user_type)) }} · {{ $row['total_marked'] }} days marked
                    </div>
                </div>
                <x-filament::badge color="danger">{{ $row['percentage'] }}%</x-filament::badge>
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-check-circle" heading="No staff below threshold" description="Every staff member with marked attendance this year is at or above 75%." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
