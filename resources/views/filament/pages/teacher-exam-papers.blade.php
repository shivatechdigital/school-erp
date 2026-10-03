<x-filament-panels::page>
    <x-filament::section heading="Exam papers you've uploaded" icon="heroicon-o-document-arrow-up">
        @forelse ($papers as $paper)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div>
                    <div class="font-medium">{{ $paper->title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $paper->schedule?->exam?->name }} · {{ $paper->schoolClass?->name }} {{ $paper->section?->name }} · {{ $paper->schedule?->subject?->name }}
                    </div>
                </div>
                <x-filament::button tag="a" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paper->file_path) }}" target="_blank" size="sm" color="gray" icon="heroicon-m-arrow-down-tray">
                    View file
                </x-filament::button>
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-document-arrow-up" heading="No exam papers yet" description="Use the Upload Exam Paper button above to share a paper with your students." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
