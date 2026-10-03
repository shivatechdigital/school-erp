<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach (['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half_day' => 'info', 'leave' => 'gray', 'holiday' => 'gray', 'not_marked' => 'gray'] as $status => $color)
                    @if (($counts[$status] ?? 0) > 0)
                        <x-filament::badge :color="$color">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $counts[$status] }}
                        </x-filament::badge>
                    @endif
                @endforeach
            </div>

            <input
                type="date"
                wire:model.live="date"
                class="rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
            />
        </div>

        <x-filament::section heading="{{ $total }} active staff">
            <div class="space-y-2">
                @foreach ($rows as $row)
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 py-2 last:border-0 dark:border-white/5">
                        <div>
                            <div class="font-medium">{{ $row['user']->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $row['user']->user_type)) }} · {{ $row['user']->department ?? '—' }}</div>
                        </div>
                        <x-filament::badge :color="match ($row['attendance']?->status) {
                            'present' => 'success',
                            'absent' => 'danger',
                            'late' => 'warning',
                            'half_day' => 'info',
                            'leave', 'holiday' => 'gray',
                            default => 'gray',
                        }">
                            {{ $row['attendance'] ? ucfirst(str_replace('_', ' ', $row['attendance']->status)) : 'Not marked' }}
                        </x-filament::badge>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
