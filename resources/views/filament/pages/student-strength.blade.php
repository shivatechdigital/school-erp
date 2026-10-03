<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-filament::icon icon="heroicon-o-user-group" class="h-5 w-5" />
                </span>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total students (school-wide)</div>
                    <div class="text-lg font-semibold">{{ $grandTotal }}</div>
                </div>
            </div>
        </x-filament::section>

        @forelse ($byClass as $className => $sections)
            <x-filament::section :heading="$className">
                <div class="space-y-2">
                    @foreach ($sections as $section)
                        <div class="flex items-center justify-between border-b border-gray-100 py-2 last:border-0 dark:border-white/5">
                            <div class="font-medium">Section {{ $section->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $section->students_count }} / {{ $section->capacity }} students</div>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-2 text-sm font-semibold">
                        <div>Class total</div>
                        <div>{{ $sections->sum('students_count') }}</div>
                    </div>
                </div>
            </x-filament::section>
        @empty
            <x-filament::empty-state icon="heroicon-o-user-group" heading="No active sections" description="Student strength will appear here once sections are set up." />
        @endforelse
    </div>
</x-filament-panels::page>
