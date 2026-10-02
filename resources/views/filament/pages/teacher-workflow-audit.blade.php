<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section heading="Temporary attendance access" icon="heroicon-o-user-plus">
            @forelse ($attendanceGrants as $grant)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-3 text-sm last:border-0 dark:border-white/5">
                    <div>
                        <span class="font-medium">{{ $grant->grantedBy?->name }}</span>
                        allowed <span class="font-medium">{{ $grant->teacher?->name }}</span>
                        to take attendance for {{ $grant->section?->class?->name }} {{ $grant->section?->name }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">{{ $grant->valid_from?->format('d M Y H:i') }} → {{ $grant->valid_until?->format('d M Y H:i') }}</span>
                        <x-filament::badge :color="$grant->revoked_at ? 'danger' : 'success'">
                            {{ $grant->revoked_at ? 'Revoked '.$grant->revoked_at->format('d M, H:i') : 'Active / expired' }}
                        </x-filament::badge>
                    </div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-user-plus" heading="No attendance cover access" description="No temporary attendance access has been assigned yet." compact />
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Student attendance changes" icon="heroicon-o-clipboard-document-check">
            @forelse ($attendanceAudits as $audit)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-3 text-sm last:border-0 dark:border-white/5">
                    <div>
                        <span class="font-medium">{{ $audit->attendance?->student?->full_name }}</span>
                        · {{ $audit->attendance?->section?->class?->name }} {{ $audit->attendance?->section?->name }}
                        · <x-filament::badge color="gray">{{ ucfirst(str_replace('_', ' ', $audit->old_status ?? 'new')) }}</x-filament::badge>
                        <x-filament::icon icon="heroicon-m-arrow-right" class="inline h-3 w-3" />
                        <x-filament::badge color="primary">{{ ucfirst(str_replace('_', ' ', $audit->new_status)) }}</x-filament::badge>
                    </div>
                    <div class="text-gray-500 dark:text-gray-400">{{ $audit->changedBy?->name ?? 'System' }} · {{ $audit->changed_at?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-clipboard-document-check" heading="No attendance changes" description="No attendance corrections have been recorded yet." compact />
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Marks changes" icon="heroicon-o-pencil-square">
            @forelse ($markAudits as $audit)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-3 text-sm last:border-0 dark:border-white/5">
                    <div>
                        <span class="font-medium">{{ $audit->mark?->student?->full_name }}</span>
                        · {{ $audit->mark?->subject?->name }}
                        · <x-filament::badge color="gray">{{ $audit->old_values['total_marks'] ?? 'New' }}</x-filament::badge>
                        <x-filament::icon icon="heroicon-m-arrow-right" class="inline h-3 w-3" />
                        <x-filament::badge color="primary">{{ $audit->new_values['total_marks'] ?? '-' }}</x-filament::badge>
                    </div>
                    <div class="text-gray-500 dark:text-gray-400">{{ $audit->changedBy?->name ?? 'System' }} · {{ $audit->changed_at?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-pencil-square" heading="No marks changes" description="No marks corrections have been recorded yet." compact />
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Timetable exchange requests" icon="heroicon-o-arrows-right-left">
            @forelse ($exchangeRequests as $exchange)
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-3 text-sm last:border-0 dark:border-white/5">
                    <div>
                        <span class="font-medium">{{ $exchange->requester?->name }}</span>
                        <x-filament::icon icon="heroicon-m-arrow-right" class="inline h-3 w-3" />
                        {{ $exchange->recipient?->name }}
                        · {{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }}
                        · {{ $exchange->requesterTimetable?->subject?->name }}
                        · <span class="italic text-gray-500 dark:text-gray-400">"{{ $exchange->reason }}"</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-filament::badge :color="match ($exchange->status) {
                            'accepted' => 'success',
                            'rejected', 'cancelled' => 'danger',
                            default => 'warning',
                        }">
                            {{ ucfirst($exchange->status) }}
                        </x-filament::badge>
                        <span class="text-gray-500 dark:text-gray-400">{{ $exchange->updated_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <x-filament::empty-state icon="heroicon-o-arrows-right-left" heading="No exchange requests" description="No timetable exchange requests have been recorded yet." compact />
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>