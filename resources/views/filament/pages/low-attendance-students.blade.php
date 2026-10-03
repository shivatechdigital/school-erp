<x-filament-panels::page>
    <x-filament::section heading="Students with attendance below {{ \App\Filament\Pages\LowAttendanceStudents::THRESHOLD }}% this year" icon="heroicon-o-exclamation-triangle">
        @forelse ($rows as $row)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div>
                    <div class="font-medium">{{ $row['student']->full_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $row['student']->admission_no }} · {{ $row['student']->section?->class?->name }} {{ $row['student']->section?->name }} · {{ $row['total_marked'] }} days marked
                    </div>
                </div>
                <x-filament::badge color="danger">{{ $row['percentage'] }}%</x-filament::badge>
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-check-circle" heading="No students below threshold" description="Every student with marked attendance this year is at or above 75%." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
