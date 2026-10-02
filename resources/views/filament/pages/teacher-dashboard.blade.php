<x-filament-panels::page>
    @php
        $initials = fn (?string $name): string => collect(explode(' ', trim((string) $name)))
            ->filter()
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        $passCount = $classResults->where('passed', true)->count();
        $failCount = $classResults->where('passed', false)->where('complete', true)->count();
        $pendingCount = $classResults->where('complete', false)->count();
        $gradedCount = $passCount + $failCount;
        $passRate = $gradedCount > 0 ? round(($passCount / $gradedCount) * 100) : 0;
    @endphp

    <div class="space-y-6">
        {{-- Hero greeting --}}
        <div class="overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 to-primary-400 px-6 py-5 text-white shadow-sm dark:from-primary-700 dark:to-primary-500">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-white/80">{{ now()->format('l, d F Y') }}</p>
                    <h2 class="mt-1 text-xl font-bold">Welcome back, {{ auth()->user()?->name }} 👋</h2>
                    <p class="mt-1 text-sm text-white/80">
                        {{ $todayTimetable->count() }} {{ \Illuminate\Support\Str::plural('period', $todayTimetable->count()) }} today ·
                        {{ $sections->count() }} assigned {{ \Illuminate\Support\Str::plural('section', $sections->count()) }}
                    </p>
                </div>
                <x-filament::icon icon="heroicon-o-academic-cap" class="h-16 w-16 text-white/20" />
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-info-50 text-info-600 dark:bg-info-500/10 dark:text-info-400">
                        <x-filament::icon icon="heroicon-o-finger-print" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">My attendance today</div>
                        <div class="text-lg font-semibold capitalize">{{ $staffAttendance?->status ?? 'Not marked' }}</div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Leave balance</div>
                        <div class="text-lg font-semibold">{{ $leaveBalance }} days</div>
                        @if ($pendingLeaves > 0)
                            <x-filament::badge color="warning" size="sm">{{ $pendingLeaves }} pending</x-filament::badge>
                        @endif
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-squares-2x2" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Assigned class sections</div>
                        <div class="text-lg font-semibold">{{ $sections->count() }}</div>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                        <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Students marked today</div>
                        <div class="text-lg font-semibold">{{ (int) $attendanceCounts->sum() }}</div>
                        @if ((int) ($attendanceCounts['absent'] ?? 0) > 0)
                            <x-filament::badge color="danger" size="sm">{{ (int) ($attendanceCounts['absent'] ?? 0) }} absent</x-filament::badge>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-filament::section heading="Today's timetable" icon="heroicon-o-calendar-days">
                    @if ($todayTimetable->isEmpty())
                        <x-filament::empty-state icon="heroicon-o-calendar" heading="No periods today" description="Enjoy your free day, nothing is scheduled." compact />
                    @else
                        <div class="space-y-2">
                            @foreach ($todayTimetable as $period)
                                <div @class([
                                    'flex flex-wrap items-center gap-3 rounded-lg border-s-4 bg-gray-50 px-4 py-3 dark:bg-white/5',
                                    'border-s-warning-500' => $period->substitute_for_id,
                                    'border-s-primary-500' => ! $period->substitute_for_id,
                                ])>
                                    <x-filament::badge color="gray" class="shrink-0 font-mono">
                                        {{ substr($period->start_time, 0, 5) }}-{{ substr($period->end_time, 0, 5) }}
                                    </x-filament::badge>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold">{{ $period->subject?->name ?? $period->break_label ?? 'Period' }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $period->schoolClass?->name }} {{ $period->section?->name }}
                                            @if ($period->room_no) · Room {{ $period->room_no }} @endif
                                        </div>
                                    </div>
                                    @if ($period->substitute_for_id)
                                        <x-filament::badge color="warning" icon="heroicon-m-arrow-path">
                                            Substitute for {{ $period->substituteFor?->name }}
                                        </x-filament::badge>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section heading="Incoming timetable exchange requests" icon="heroicon-o-arrows-right-left">
                    @forelse ($incomingExchanges as $exchange)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                    {{ $initials($exchange->requester?->name) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-medium">{{ $exchange->requester?->name }} requests an exchange</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $exchange->requesterTimetable?->schoolClass?->name }} {{ $exchange->requesterTimetable?->section?->name }} · {{ $exchange->requesterTimetable?->subject?->name }} · {{ $exchange->requesterTimetable?->start_time }}</div>
                                    <div class="mt-1 text-sm italic text-gray-500 dark:text-gray-400">"{{ $exchange->reason }}"</div>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <x-filament::button size="sm" color="success" icon="heroicon-m-check" wire:click="acceptExchange({{ $exchange->id }})">Accept</x-filament::button>
                                <x-filament::button size="sm" color="gray" icon="heroicon-m-x-mark" wire:click="rejectExchange({{ $exchange->id }})">Decline</x-filament::button>
                            </div>
                        </div>
                    @empty
                        <x-filament::empty-state icon="heroicon-o-arrows-right-left" heading="No pending requests" description="You have no incoming exchange requests right now." compact />
                    @endforelse
                    @foreach ($outgoingExchanges as $exchange)
                        <div class="mt-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                            Waiting for {{ $exchange->recipient?->name }} to respond to your exchange request.
                        </div>
                    @endforeach
                </x-filament::section>

                <x-filament::section heading="Marks awaiting your approval" icon="heroicon-o-check-circle">
                    @forelse ($markApprovals as $approval)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                            <div>
                                <div class="font-medium">{{ $approval->student?->full_name }} · {{ $approval->examSchedule?->subject?->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $approval->examSchedule?->exam?->name }} · submitted by {{ $approval->requester?->name }} ·
                                    Theory {{ $approval->theory_marks ?? '-' }}, Practical {{ $approval->practical_marks ?? '-' }}
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <x-filament::button size="sm" color="success" icon="heroicon-m-check" wire:click="approveMarkRequest({{ $approval->id }})">Approve</x-filament::button>
                                <x-filament::button size="sm" color="gray" icon="heroicon-m-x-mark" wire:click="rejectMarkRequest({{ $approval->id }})">Reject</x-filament::button>
                            </div>
                        </div>
                    @empty
                        <x-filament::empty-state icon="heroicon-o-check-circle" heading="All caught up" description="No marks are waiting for your approval." compact />
                    @endforelse
                </x-filament::section>

                <x-filament::section heading="Class performance" icon="heroicon-o-chart-bar">
                    @if (! $latestExam)
                        <x-filament::empty-state icon="heroicon-o-chart-bar" heading="No completed exam yet" description="Results will appear here once an exam is marked completed." compact />
                    @elseif ($classResults->isEmpty())
                        <x-filament::empty-state icon="heroicon-o-chart-bar" heading="No marks entered" description="No marks have been entered yet for {{ $latestExam->name }}." compact />
                    @else
                        <div class="mb-4">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium">{{ $latestExam->name }} pass rate</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $passRate }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-2 rounded-full bg-success-500" style="width: {{ $passRate }}%"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <x-filament::badge color="success" icon="heroicon-m-check-circle">{{ $passCount }} pass</x-filament::badge>
                                <x-filament::badge color="danger" icon="heroicon-m-x-circle">{{ $failCount }} fail</x-filament::badge>
                                <x-filament::badge color="gray" icon="heroicon-m-clock">{{ $pendingCount }} pending</x-filament::badge>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @foreach ($classResults as $result)
                                <div class="flex items-start justify-between gap-3 border-b border-gray-100 py-2 last:border-0 dark:border-white/5">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ $result['student']?->full_name }} <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $result['student']?->section?->name }}</span></div>
                                        @if ($result['failed_subjects']->isNotEmpty())
                                            <div class="text-danger-600 dark:text-danger-400">Fail: {{ $result['failed_subjects']->join(', ') }}</div>
                                        @endif
                                    </div>
                                    <x-filament::badge :color="$result['complete'] ? ($result['passed'] ? 'success' : 'danger') : 'gray'">
                                        {{ $result['complete'] ? ($result['passed'] ? 'Pass' : 'Fail') : 'Pending' }}
                                    </x-filament::badge>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section heading="My students" icon="heroicon-o-users">
                    @forelse ($classStudents as $student)
                        <div class="flex items-start gap-3 border-b border-gray-100 py-3 last:border-0 dark:border-white/5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                {{ $initials($student->full_name) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-medium">{{ $student->full_name }} <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $student->section?->name }} · Roll {{ $student->roll_no ?? '-' }}</span></div>
                                    <div class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                        <x-filament::icon icon="heroicon-m-phone" class="h-4 w-4" />
                                        {{ $student->phone ?: $student->guardians->firstWhere('pivot.is_primary', true)?->phone ?: 'No contact listed' }}
                                    </div>
                                </div>
                                @foreach ($student->notes as $note)
                                    <div class="mt-1 rounded-md bg-gray-50 px-2 py-1 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $note->created_at?->format('d M') }}:</span> {{ $note->note }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <x-filament::empty-state icon="heroicon-o-users" heading="No students assigned" description="Students will appear here once assigned to your class sections." compact />
                    @endforelse
                </x-filament::section>
            </div>

            <div class="space-y-6">
                <x-filament::section heading="Teaching tools" icon="heroicon-o-squares-2x2">
                    <div class="grid grid-cols-1 gap-3">
                        <a href="{{ $links['attendance'] }}" class="group flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-400 hover:bg-primary-50 dark:border-white/10 dark:hover:bg-primary-500/10">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 group-hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400">
                                <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-5 w-5" />
                            </span>
                            <span class="font-medium">Student attendance</span>
                        </a>
                        <a href="{{ $links['homework'] }}" class="group flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-400 hover:bg-primary-50 dark:border-white/10 dark:hover:bg-primary-500/10">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info-50 text-info-600 group-hover:bg-info-100 dark:bg-info-500/10 dark:text-info-400">
                                <x-filament::icon icon="heroicon-o-book-open" class="h-5 w-5" />
                            </span>
                            <span class="font-medium">Homework and submissions</span>
                        </a>
                        <a href="{{ $links['marks'] }}" class="group flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-400 hover:bg-primary-50 dark:border-white/10 dark:hover:bg-primary-500/10">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 group-hover:bg-warning-100 dark:bg-warning-500/10 dark:text-warning-400">
                                <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                            </span>
                            <span class="font-medium">Marks entry</span>
                        </a>
                    </div>
                </x-filament::section>

                <x-filament::section heading="Temporary attendance cover" icon="heroicon-o-user-plus">
                    @forelse ($activeGrants as $grant)
                        <div class="flex items-start justify-between gap-3 border-b border-gray-100 py-2 last:border-0 dark:border-white/5">
                            <div class="text-sm">
                                <div class="font-medium">{{ $grant->section?->class?->name }} {{ $grant->section?->name }} · {{ $grant->teacher?->name }}</div>
                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                    <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                                    Until {{ $grant->valid_until?->format('d M, H:i') }}
                                </div>
                            </div>
                            <x-filament::icon-button icon="heroicon-m-x-mark" color="danger" label="Revoke access" wire:click="revokeAttendanceGrant({{ $grant->id }})" />
                        </div>
                    @empty
                        <x-filament::empty-state icon="heroicon-o-user-plus" heading="No active cover" description="You haven't granted anyone temporary attendance access." compact />
                    @endforelse
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>