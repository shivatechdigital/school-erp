<x-filament-panels::page>
    @if ($session)
        <div class="grid gap-4 sm:grid-cols-3">
            <x-filament::section heading="Session">
                <div class="text-xl font-semibold sm:text-2xl">{{ $session->name }}</div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $session->start_date->format('d M Y') }} &ndash; {{ $session->end_date->format('d M Y') }}
                </p>
            </x-filament::section>

            <x-filament::section heading="Progress" class="sm:col-span-2">
                <div class="flex items-center gap-4">
                    <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div
                            class="h-full rounded-full bg-primary-500 transition-all"
                            style="width: {{ $progress['percent'] }}%"
                        ></div>
                    </div>
                    <span class="shrink-0 text-sm font-semibold">{{ $progress['percent'] }}%</span>
                </div>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    @if ($progress['daysRemaining'] > 0)
                        <span class="font-semibold text-gray-950 dark:text-white">{{ $progress['daysRemaining'] }}</span> days remaining in this session.
                    @else
                        This session has ended.
                    @endif
                </p>
            </x-filament::section>
        </div>
    @else
        <x-filament::empty-state
            icon="heroicon-o-calendar-date-range"
            heading="No academic session set yet"
            description="Set Session button se session ka start aur end month/year define karein. Holiday Calendar isi session tak scroll ho sakega."
        />
    @endif
</x-filament-panels::page>
