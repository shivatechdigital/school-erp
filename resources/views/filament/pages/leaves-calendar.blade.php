<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <x-filament::icon-button icon="heroicon-m-chevron-left" label="Previous month" wire:click="previousMonth" />
            <h2 class="text-lg font-bold">{{ $monthLabel }}</h2>
            <x-filament::icon-button icon="heroicon-m-chevron-right" label="Next month" wire:click="nextMonth" />
        </div>

        @forelse ($byDate as $entry)
            <x-filament::section :heading="$entry['date']->format('l, d M Y')">
                <div class="space-y-2">
                    @foreach ($entry['leaves'] as $leave)
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2 last:border-0 dark:border-white/5">
                            <div class="font-medium">{{ $leave->user?->name }}</div>
                            <x-filament::badge color="warning">{{ $leave->leaveType?->name }}</x-filament::badge>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @empty
            <x-filament::empty-state icon="heroicon-o-calendar" heading="No approved leaves this month" description="Staff on approved leave will appear here, grouped by date." />
        @endforelse
    </div>
</x-filament-panels::page>
