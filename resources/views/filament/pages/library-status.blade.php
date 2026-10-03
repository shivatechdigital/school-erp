<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Books</div>
                <div class="text-2xl font-bold">{{ $totalBooks }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Available Copies</div>
                <div class="text-2xl font-bold">{{ $availableBooks }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Currently Issued</div>
                <div class="text-2xl font-bold">{{ $issuedCount }}</div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Overdue (Defaulters)" icon="heroicon-o-exclamation-triangle">
            @forelse ($defaulters as $issue)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                    <div>
                        <div class="font-medium">{{ $issue->book?->title }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Borrower: {{ $issue->member_type === 'student' ? $issue->student?->full_name : $issue->staff?->name }}
                        </div>
                    </div>
                    <x-filament::badge color="danger">
                        {{ now()->diffInDays($issue->due_date) }} days overdue
                    </x-filament::badge>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-check-circle" heading="No overdue books" description="Every issued book is within its due date." compact />
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
