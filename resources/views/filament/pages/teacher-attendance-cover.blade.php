<x-filament-panels::page>
    <x-filament::section heading="Active attendance cover grants" icon="heroicon-o-user-plus">
        @forelse ($activeGrants as $grant)
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div class="text-sm">
                    <div class="font-medium">{{ $grant->section?->class?->name }} {{ $grant->section?->name }} · {{ $grant->teacher?->name }}</div>
                    <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                        Until {{ $grant->valid_until?->format('d M, H:i') }}
                    </div>
                    @if ($grant->reason)
                        <div class="mt-1 italic text-gray-500 dark:text-gray-400">"{{ $grant->reason }}"</div>
                    @endif
                </div>
                <x-filament::icon-button icon="heroicon-m-x-mark" color="danger" label="Revoke access" wire:click="revokeAttendanceGrant({{ $grant->id }})" />
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-user-plus" heading="No active cover" description="Use the Assign Attendance Cover button above to give a teacher temporary access to mark attendance for your section." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
