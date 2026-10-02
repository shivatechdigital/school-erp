<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="text-sm text-gray-500">My attendance today</div>
                <div class="mt-2 text-xl font-semibold">{{ ucfirst($staffAttendance?->status ?? 'Not marked') }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Leave balance</div>
                <div class="mt-2 text-xl font-semibold">{{ $leaveBalance }} days</div>
                <div class="text-sm text-gray-500">{{ $pendingLeaves }} pending request(s)</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Assigned class sections</div>
                <div class="mt-2 text-xl font-semibold">{{ $sections->count() }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">Students marked today</div>
                <div class="mt-2 text-xl font-semibold">{{ (int) $attendanceCounts->sum() }}</div>
                <div class="text-sm text-gray-500">{{ (int) ($attendanceCounts['absent'] ?? 0) }} absent</div>
            </x-filament::section>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-filament::section heading="Today's timetable" icon="heroicon-o-calendar-days">
                    @if ($todayTimetable->isEmpty())
                        <p class="text-sm text-gray-500">No periods are scheduled for today.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-start text-sm">
                                <thead><tr class="border-b text-gray-500"><th class="py-2 pe-4">Time</th><th class="py-2 pe-4">Class</th><th class="py-2 pe-4">Subject</th><th class="py-2 pe-4">Room</th><th class="py-2">Assignment</th></tr></thead>
                                <tbody>
                                    @foreach ($todayTimetable as $period)
                                        <tr class="border-b last:border-0">
                                            <td class="py-3 pe-4 whitespace-nowrap">{{ substr($period->start_time, 0, 5) }}-{{ substr($period->end_time, 0, 5) }}</td>
                                            <td class="py-3 pe-4">{{ $period->schoolClass?->name }} {{ $period->section?->name }}</td>
                                            <td class="py-3 pe-4">{{ $period->subject?->name ?? $period->break_label ?? 'Period' }}</td>
                                            <td class="py-3 pe-4">{{ $period->room_no ?: '-' }}</td>
                                            <td class="py-3">@if ($period->substitute_for_id)<span class="text-warning-600">Substitute for {{ $period->substituteFor?->name }}</span>@else Regular @endif</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section heading="Incoming timetable exchange requests" icon="heroicon-o-arrows-right-left">
                    @forelse ($incomingExchanges as $exchange)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b py-3 last:border-0">
                            <div class="min-w-0">
                                <div class="font-medium">{{ $exchange->requester?->name }} requests an exchange</div>
                                <div class="text-sm text-gray-500">{{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }} · {{ $exchange->requesterTimetable?->subject?->name }} · {{ $exchange->requesterTimetable?->start_time }}. Reason: {{ $exchange->reason }}</div>
                            </div>
                            <div class="flex gap-2">
                                <x-filament::button size="sm" color="success" wire:click="acceptExchange({{ $exchange->id }})">Accept</x-filament::button>
                                <x-filament::button size="sm" color="gray" wire:click="rejectExchange({{ $exchange->id }})">Decline</x-filament::button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No pending exchange requests.</p>
                    @endforelse
                    @foreach ($outgoingExchanges as $exchange)
                        <p class="mt-2 text-sm text-gray-500">Waiting for {{ $exchange->recipient?->name }} to respond to your exchange request.</p>
                    @endforeach
                </x-filament::section>

                <x-filament::section heading="Marks awaiting your approval" icon="heroicon-o-check-circle">
                    @forelse ($markApprovals as $approval)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b py-3 last:border-0">
                            <div>
                                <div class="font-medium">{{ $approval->student?->full_name }} · {{ $approval->examSchedule?->subject?->name }}</div>
                                <div class="text-sm text-gray-500">{{ $approval->examSchedule?->exam?->name }} · submitted by {{ $approval->requester?->name }} · Theory {{ $approval->theory_marks ?? '-' }}, Practical {{ $approval->practical_marks ?? '-' }}</div>
                            </div>
                            <div class="flex gap-2">
                                <x-filament::button size="sm" color="success" wire:click="approveMarkRequest({{ $approval->id }})">Approve</x-filament::button>
                                <x-filament::button size="sm" color="gray" wire:click="rejectMarkRequest({{ $approval->id }})">Reject</x-filament::button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No marks are waiting for approval.</p>
                    @endforelse
                </x-filament::section>

                <x-filament::section heading="Class performance" icon="heroicon-o-chart-bar">
                    @if (! $latestExam)
                        <p class="text-sm text-gray-500">No completed exam is available yet.</p>
                    @elseif ($classResults->isEmpty())
                        <p class="text-sm text-gray-500">No marks have been entered for {{ $latestExam->name }}.</p>
                    @else
                        <p class="mb-3 text-sm text-gray-500">{{ $latestExam->name }} · {{ $classResults->where('passed', true)->count() }} pass · {{ $classResults->where('passed', false)->where('complete', true)->count() }} fail · {{ $classResults->where('complete', false)->count() }} awaiting marks</p>
                        <div class="space-y-2">
                            @foreach ($classResults as $result)
                                <div class="flex items-start justify-between gap-3 border-b py-2 last:border-0">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ $result['student']?->full_name }} · {{ $result['student']?->section?->name }}</div>
                                        @if ($result['failed_subjects']->isNotEmpty())
                                            <div class="text-danger-600">Fail: {{ $result['failed_subjects']->join(', ') }}</div>
                                        @endif
                                    </div>
                                    <span @class(['text-sm font-medium', 'text-success-600' => $result['passed'], 'text-danger-600' => ! $result['passed'] && $result['complete'], 'text-gray-500' => ! $result['complete']])>{{ $result['complete'] ? ($result['passed'] ? 'Pass' : 'Fail') : 'Pending' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section heading="My students" icon="heroicon-o-users">
                    @forelse ($classStudents as $student)
                        <div class="border-b py-3 last:border-0">
                            <div class="flex flex-wrap justify-between gap-2">
                                <div class="font-medium">{{ $student->full_name }} <span class="font-normal text-gray-500">· {{ $student->section?->name }} · Roll {{ $student->roll_no ?? '-' }}</span></div>
                                <div class="text-sm text-gray-500">{{ $student->phone ?: $student->guardians->firstWhere('pivot.is_primary', true)?->phone ?: 'No contact listed' }}</div>
                            </div>
                            @foreach ($student->notes as $note)
                                <div class="mt-1 text-sm text-gray-500">{{ $note->created_at?->format('d M') }} · {{ $note->note }}</div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No students are assigned to your class sections.</p>
                    @endforelse
                </x-filament::section>
            </div>

            <div class="space-y-6">
                <x-filament::section heading="Teaching tools" icon="heroicon-o-squares-2x2">
                    <div class="flex flex-col gap-2">
                        <x-filament::button tag="a" href="{{ $links['attendance'] }}" icon="heroicon-o-clipboard-document-check">Student attendance</x-filament::button>
                        <x-filament::button tag="a" href="{{ $links['homework'] }}" color="gray" icon="heroicon-o-book-open">Homework and submissions</x-filament::button>
                        <x-filament::button tag="a" href="{{ $links['marks'] }}" color="gray" icon="heroicon-o-pencil-square">Marks entry</x-filament::button>
                    </div>
                </x-filament::section>

                <x-filament::section heading="Temporary attendance cover" icon="heroicon-o-user-plus">
                    @forelse ($activeGrants as $grant)
                        <div class="flex items-start justify-between gap-3 border-b py-2 last:border-0">
                            <div class="text-sm">
                                <div class="font-medium">{{ $grant->section?->class?->name }} {{ $grant->section?->name }} · {{ $grant->teacher?->name }}</div>
                                <div class="text-gray-500">Until {{ $grant->valid_until?->format('d M, H:i') }}</div>
                            </div>
                            <x-filament::icon-button icon="heroicon-m-x-mark" color="danger" label="Revoke access" wire:click="revokeAttendanceGrant({{ $grant->id }})" />
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No active attendance cover grants.</p>
                    @endforelse
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>