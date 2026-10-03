<x-filament-panels::page>
    <div class="space-y-6">
        @forelse ($exams as $exam)
            <x-filament::section>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="font-bold">{{ $exam->name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $exam->start_date?->format('d M Y') }} &rarr; {{ $exam->end_date?->format('d M Y') }}
                        </div>
                    </div>
                    <x-filament::badge :color="$exam->status === 'ongoing' ? 'warning' : 'info'">
                        {{ ucfirst($exam->status) }}
                    </x-filament::badge>
                </div>

                @if ($exam->schedules->isNotEmpty())
                    <div class="mt-3 space-y-1">
                        @foreach ($exam->schedules->sortBy('exam_date') as $schedule)
                            <div class="flex items-center justify-between border-b border-gray-100 py-1.5 text-sm last:border-0 dark:border-white/5">
                                <div>{{ $schedule->class?->name }} &middot; {{ $schedule->subject?->name }}</div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ $schedule->exam_date?->format('d M Y') }}
                                    @if ($schedule->start_time) · {{ substr($schedule->start_time, 0, 5) }}-{{ substr($schedule->end_time, 0, 5) }} @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        @empty
            <x-filament::empty-state icon="heroicon-o-document-chart-bar" heading="No upcoming exams" description="Exams marked as upcoming or ongoing will appear here." />
        @endforelse
    </div>
</x-filament-panels::page>
