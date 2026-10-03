<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-calendar" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Allocated ({{ $year }})</div>
                        <div class="text-lg font-semibold">{{ $totalAllocated }} days</div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                        <x-filament::icon icon="heroicon-o-minus-circle" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Used (approved)</div>
                        <div class="text-lg font-semibold">{{ $totalUsed }} days</div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Pending approval</div>
                        <div class="text-lg font-semibold">{{ $totalPending }} days</div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Remaining balance</div>
                        <div class="text-lg font-semibold">{{ $totalRemaining }} days</div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Balance by leave type" icon="heroicon-o-squares-2x2">
            @if ($breakdown->isEmpty())
                <x-filament::empty-state icon="heroicon-o-calendar" heading="No leave types configured" description="Ask your admin to set up leave types for your school." compact />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-start text-gray-500 dark:border-white/5 dark:text-gray-400">
                                <th class="py-2 text-start font-medium">Leave type</th>
                                <th class="py-2 text-end font-medium">Allocated</th>
                                <th class="py-2 text-end font-medium">Used</th>
                                <th class="py-2 text-end font-medium">Pending</th>
                                <th class="py-2 text-end font-medium">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($breakdown as $row)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                    <td class="py-2">{{ $row['type']->name }}</td>
                                    <td class="py-2 text-end">{{ $row['allocated'] }}</td>
                                    <td class="py-2 text-end">{{ $row['used'] }}</td>
                                    <td class="py-2 text-end">{{ $row['pending'] }}</td>
                                    <td class="py-2 text-end font-semibold">{{ $row['remaining'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="My leave requests ({{ $year }})" icon="heroicon-o-list-bullet">
            @forelse ($leaves as $leave)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                    <div>
                        <div class="font-medium">{{ $leave->leaveType?->name }} · {{ $leave->total_days }} {{ \Illuminate\Support\Str::plural('day', $leave->total_days) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $leave->from_date?->format('d M Y') }} - {{ $leave->to_date?->format('d M Y') }}
                        </div>
                        <div class="mt-1 text-sm italic text-gray-500 dark:text-gray-400">"{{ $leave->reason }}"</div>
                    </div>
                    <x-filament::badge :color="match ($leave->status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }">
                        {{ ucfirst($leave->status) }}
                    </x-filament::badge>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-calendar" heading="No leave requests yet" description="Use the Apply for Leave button above to submit your first request." compact />
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>
