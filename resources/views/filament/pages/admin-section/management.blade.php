<x-filament-panels::page>
    <div class="grid min-w-0 grid-cols-1 gap-6 xl:grid-cols-[minmax(17rem,3fr)_minmax(0,7fr)]">
        <section class="min-w-0 space-y-5 border-b border-gray-200 pb-6 dark:border-white/10 xl:border-b-0 xl:border-r xl:pb-0 xl:pr-6">
            <header class="space-y-1">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $this->formHeading() }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Add a new record or select Edit from the list.</p>
            </header>

            {{ $this->form }}

            <div class="flex flex-wrap gap-2">
                <x-filament::button wire:click="saveRecord" wire:loading.attr="disabled" wire:target="saveRecord">
                    {{ $this->submitLabel() }}
                </x-filament::button>

                @if ($editingRecordId)
                    <x-filament::button color="gray" wire:click="resetForm">Cancel</x-filament::button>
                @endif
            </div>
        </section>

        <section class="min-w-0 space-y-4">
            <header>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $this->recordLabel() }} Records</h2>
            </header>

            <div class="min-w-0 overflow-x-auto">
                {{ $this->table }}
            </div>
        </section>
    </div>
</x-filament-panels::page>