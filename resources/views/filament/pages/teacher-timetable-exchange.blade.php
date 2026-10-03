<x-filament-panels::page>
    @php
        $initials = fn (?string $name): string => collect(explode(' ', trim((string) $name)))
            ->filter()
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    @endphp

    <div class="space-y-6">
        <x-filament::section heading="Incoming exchange requests" icon="heroicon-o-arrows-right-left">
            @forelse ($incomingExchanges as $exchange)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            {{ $initials($exchange->requester?->name) }}
                        </span>
                        <div class="min-w-0">
                            <div class="font-medium">{{ $exchange->requester?->name }} requests an exchange</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }} · {{ $exchange->requesterTimetable?->subject?->name }} · {{ $exchange->requesterTimetable?->start_time }}</div>
                            <div class="mt-1 text-sm italic text-gray-500 dark:text-gray-400">"{{ $exchange->reason }}"</div>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <x-filament::button size="sm" color="success" icon="heroicon-m-check" wire:click="acceptExchange({{ $exchange->id }})">Accept</x-filament::button>
                        <x-filament::button size="sm" color="gray" icon="heroicon-m-x-mark" wire:click="rejectExchange({{ $exchange->id }})">Decline</x-filament::button>
                    </div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-arrows-right-left" heading="No pending requests" description="You have no incoming exchange requests right now." compact />
            @endforelse
        </x-filament::section>

        <x-filament::section heading="My outgoing requests" icon="heroicon-o-paper-airplane">
            @forelse ($outgoingExchanges as $exchange)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                    <div>
                        <div class="font-medium">{{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }} · {{ $exchange->requesterTimetable?->subject?->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">To {{ $exchange->recipient?->name }} · "{{ $exchange->reason }}"</div>
                    </div>
                    <x-filament::badge :color="match ($exchange->status) {
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }">
                        {{ ucfirst($exchange->status) }}
                    </x-filament::badge>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-paper-airplane" heading="No outgoing requests" description="Requests you send will appear here." compact />
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
