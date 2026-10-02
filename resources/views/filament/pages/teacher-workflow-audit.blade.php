<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section heading="Temporary attendance access" icon="heroicon-o-user-plus">
            @forelse ($attendanceGrants as $grant)
                <div class="flex flex-wrap justify-between gap-2 border-b py-2 text-sm last:border-0">
                    <div><span class="font-medium">{{ $grant->grantedBy?->name }}</span> allowed {{ $grant->teacher?->name }} to take attendance for {{ $grant->section?->class?->name }} {{ $grant->section?->name }}</div>
                    <div class="text-gray-500">{{ $grant->valid_from?->format('d M Y H:i') }} to {{ $grant->valid_until?->format('d M Y H:i') }} · {{ $grant->revoked_at ? 'Revoked '.$grant->revoked_at->format('d M Y H:i') : 'Active / expired' }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No attendance cover access has been assigned.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Student attendance changes" icon="heroicon-o-clipboard-document-check">
            @forelse ($attendanceAudits as $audit)
                <div class="flex flex-wrap justify-between gap-2 border-b py-2 text-sm last:border-0">
                    <div>
                        <span class="font-medium">{{ $audit->attendance?->student?->full_name }}</span>
                        · {{ $audit->attendance?->section?->class?->name }} {{ $audit->attendance?->section?->name }}
                        · {{ ucfirst(str_replace('_', ' ', $audit->old_status ?? 'new')) }} → {{ ucfirst(str_replace('_', ' ', $audit->new_status)) }}
                    </div>
                    <div class="text-gray-500">{{ $audit->changedBy?->name ?? 'System' }} · {{ $audit->changed_at?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No attendance changes recorded.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Marks changes" icon="heroicon-o-pencil-square">
            @forelse ($markAudits as $audit)
                <div class="flex flex-wrap justify-between gap-2 border-b py-2 text-sm last:border-0">
                    <div>
                        <span class="font-medium">{{ $audit->mark?->student?->full_name }}</span>
                        · {{ $audit->mark?->subject?->name }}
                        · {{ $audit->old_values['total_marks'] ?? 'New' }} → {{ $audit->new_values['total_marks'] ?? '-' }}
                    </div>
                    <div class="text-gray-500">{{ $audit->changedBy?->name ?? 'System' }} · {{ $audit->changed_at?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No marks changes recorded.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Timetable exchange requests" icon="heroicon-o-arrows-right-left">
            @forelse ($exchangeRequests as $exchange)
                <div class="flex flex-wrap justify-between gap-2 border-b py-2 text-sm last:border-0">
                    <div>
                        <span class="font-medium">{{ $exchange->requester?->name }}</span>
                        → {{ $exchange->recipient?->name }} · {{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }}
                        · {{ $exchange->requesterTimetable?->subject?->name }} · {{ $exchange->reason }}
                    </div>
                    <div class="text-gray-500">{{ ucfirst($exchange->status) }} · {{ $exchange->updated_at?->format('d M Y H:i') }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No timetable exchange requests recorded.</p>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-panels::page>