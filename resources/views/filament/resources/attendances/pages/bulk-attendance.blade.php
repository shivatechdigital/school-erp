<x-filament-panels::page>

    <form wire:submit="saveAttendance">

        {{ $this->form }}

        @if (count($studentList) > 0)
            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        📋 Student List
                        <span class="text-sm font-normal text-gray-500">
                            ({{ count($studentList) }} students)
                        </span>
                    </h3>
                </div>

                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Roll</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Student Name</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($studentList as $index => $student)
                            <tr wire:key="student-{{ $student['id'] }}" class="transition hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-600 dark:text-gray-300">
                                    {{ $student['roll_no'] }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white">
                                    {{ $student['name'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <select
                                        wire:model.live="studentList.{{ $index }}.status"
                                        @class([
                                            'rounded-lg border px-3 py-1.5 text-sm font-medium',
                                            'border-green-300 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' => $student['status'] === 'present',
                                            'border-red-300 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400' => $student['status'] === 'absent',
                                            'border-yellow-300 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' => $student['status'] === 'late',
                                            'border-blue-300 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' => $student['status'] === 'half_day',
                                            'border-gray-300 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => ! in_array($student['status'], ['present', 'absent', 'late', 'half_day'], true),
                                        ])
                                    >
                                        <option value="present">✅ Present</option>
                                        <option value="absent">❌ Absent</option>
                                        <option value="late">⏰ Late</option>
                                        <option value="half_day">🕐 Half Day</option>
                                        <option value="leave">📋 Leave</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-sm">
                <span class="rounded-full bg-green-100 px-3 py-1 font-medium text-green-700">
                    ✅ Present: {{ collect($studentList)->where('status', 'present')->count() }}
                </span>
                <span class="rounded-full bg-red-100 px-3 py-1 font-medium text-red-700">
                    ❌ Absent: {{ collect($studentList)->where('status', 'absent')->count() }}
                </span>
                <span class="rounded-full bg-yellow-100 px-3 py-1 font-medium text-yellow-700">
                    ⏰ Late: {{ collect($studentList)->where('status', 'late')->count() }}
                </span>
            </div>

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    type="submit"
                    color="primary"
                    size="lg"
                    icon="heroicon-o-check-circle"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="saveAttendance">💾 Save Attendance</span>
                    <span wire:loading wire:target="saveAttendance">Saving...</span>
                </x-filament::button>
            </div>

        @elseif ($this->data['section_id'] ?? null)
            <div class="mt-6 py-10 text-center text-gray-500">
                <p class="text-lg">😕 No active students in this section</p>
            </div>
        @else
            <div class="mt-6 py-10 text-center text-gray-400">
                <x-heroicon-o-clipboard-document-check class="mx-auto mb-3 h-12 w-12" />
                <p class="text-lg">Select Class & Section to load students</p>
            </div>
        @endif

    </form>

</x-filament-panels::page>
