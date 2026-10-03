<x-filament-panels::page>
    <x-filament::section heading="Notices you've sent" icon="heroicon-o-megaphone">
        @forelse ($notices as $notice)
            <div class="border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="font-medium">{{ $notice->title }}</div>
                    <x-filament::badge :color="match ($notice->priority) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        default => 'gray',
                    }">
                        {{ ucfirst($notice->priority) }}
                    </x-filament::badge>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $notice->schoolClass?->name }} {{ $notice->section?->name }} · {{ $notice->publish_date?->format('d M Y') }}
                </div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $notice->content }}</div>
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-megaphone" heading="No notices yet" description="Use the Send Class Notice button above to publish an announcement to your class." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
