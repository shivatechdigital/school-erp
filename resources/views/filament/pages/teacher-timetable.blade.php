<x-filament-panels::page>
    @php
        $palette = [
            ['bg' => 'bg-primary-50 dark:bg-primary-500/10', 'border' => 'border-s-primary-500', 'text' => 'text-primary-700 dark:text-primary-300'],
            ['bg' => 'bg-success-50 dark:bg-success-500/10', 'border' => 'border-s-success-500', 'text' => 'text-success-700 dark:text-success-300'],
            ['bg' => 'bg-warning-50 dark:bg-warning-500/10', 'border' => 'border-s-warning-500', 'text' => 'text-warning-700 dark:text-warning-300'],
            ['bg' => 'bg-info-50 dark:bg-info-500/10', 'border' => 'border-s-info-500', 'text' => 'text-info-700 dark:text-info-300'],
            ['bg' => 'bg-danger-50 dark:bg-danger-500/10', 'border' => 'border-s-danger-500', 'text' => 'text-danger-700 dark:text-danger-300'],
        ];
    @endphp

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/5">
            <h2 class="text-lg font-bold text-gray-950 dark:text-white">Schedule</h2>

            <select
                wire:model.live="range"
                class="rounded-lg border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200"
            >
                <option value="today">Today</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>
        </div>

        @if ($grid->isEmpty())
            <x-filament::empty-state icon="heroicon-o-calendar" heading="No periods scheduled" description="Your timetable will appear here once periods are assigned to you." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="w-20 border-b border-gray-100 p-3 dark:border-white/5"></th>
                            @foreach ($days as $day)
                                <th @class([
                                    'min-w-[140px] border-b border-s border-gray-100 p-3 text-center dark:border-white/5',
                                    'bg-primary-50/60 dark:bg-primary-500/10' => $day === $today && $range !== 'today',
                                ])>
                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                        {{ substr($day, 0, 2) }}
                                    </div>
                                    <div @class([
                                        'mt-0.5 text-sm font-bold',
                                        'text-primary-600 dark:text-primary-400' => $day === $today,
                                        'text-gray-700 dark:text-gray-200' => $day !== $today,
                                    ])>
                                        {{ ucfirst($day) }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grid as $row)
                            <tr>
                                <td class="border-b border-gray-100 p-3 align-top font-mono text-xs font-semibold text-gray-400 dark:border-white/5 dark:text-gray-500">
                                    {{ substr($row['start'], 0, 5) }}
                                </td>
                                @foreach ($days as $day)
                                    @php $period = $row['days'][$day] ?? null; @endphp
                                    <td class="border-b border-s border-gray-100 p-2 align-top dark:border-white/5">
                                        @if ($period)
                                            @if ($period->is_break)
                                                <div class="rounded-lg bg-gray-100 px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                                    {{ $period->break_label ?: 'Break' }}
                                                </div>
                                            @else
                                                @php $color = $palette[($period->subject_id ?? 0) % count($palette)]; @endphp
                                                <div @class([
                                                    "rounded-xl border-s-4 px-3 py-2 {$color['bg']}",
                                                    'border-s-warning-500' => $period->substitute_for_id,
                                                    $color['border'] => ! $period->substitute_for_id,
                                                ])>
                                                    <div class="text-xs font-bold {{ $color['text'] }}">
                                                        {{ $period->schoolClass?->name }}-{{ $period->section?->name }}
                                                    </div>
                                                    <div class="mt-0.5 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                                        {{ $period->subject?->name }}
                                                    </div>
                                                    @if ($period->room_no)
                                                        <div class="text-[11px] text-gray-400 dark:text-gray-500">Room {{ $period->room_no }}</div>
                                                    @endif
                                                    @if ($period->substitute_for_id)
                                                        <div class="mt-1 flex items-center gap-1 text-[11px] text-warning-600 dark:text-warning-400">
                                                            <x-filament::icon icon="heroicon-m-arrow-path" class="h-3 w-3" />
                                                            Substitute for {{ $period->substituteFor?->name }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
