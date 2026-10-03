<x-filament-panels::page>
    <x-filament::section heading="Notes you've added" icon="heroicon-o-pencil-square">
        @forelse ($notes as $note)
            <div class="border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                <div class="font-medium">{{ $note->student?->full_name }} <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $note->created_at?->format('d M Y, H:i') }}</span></div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $note->note }}</div>
            </div>
        @empty
            <x-filament::empty-state icon="heroicon-o-pencil-square" heading="No notes yet" description="Use the Add Student Note button above to record observations about your students." compact />
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
