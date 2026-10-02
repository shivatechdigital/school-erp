<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        @if (! empty($studentList))
            @php
                $filledCount = collect($studentList)->filter(fn (array $s): bool => filled($s['theory_marks'] ?? null) || filled($s['practical_marks'] ?? null))->count();
            @endphp

            <x-filament::section icon="heroicon-o-pencil-square">
                <x-slot name="heading">
                    <div class="flex flex-wrap items-center gap-2">
                        <span>Student marks</span>
                        <x-filament::badge color="success">{{ $filledCount }} / {{ count($studentList) }} entered</x-filament::badge>
                    </div>
                </x-slot>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full text-start text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <th class="px-3 py-2 text-center">Roll</th>
                                <th class="px-3 py-2">Student</th>
                                <th class="px-3 py-2">Section</th>
                                <th class="px-3 py-2">Theory</th>
                                <th class="px-3 py-2">Practical</th>
                                <th class="px-3 py-2">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($studentList as $index => $student)
                                <tr class="odd:bg-white even:bg-gray-50/60 dark:odd:bg-transparent dark:even:bg-white/5">
                                    <td class="px-3 py-2 text-center">
                                        <x-filament::badge color="gray">{{ $student['roll_no'] }}</x-filament::badge>
                                    </td>
                                    <td class="px-3 py-2 font-medium">{{ $student['name'] }}</td>
                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $student['section'] }}</td>
                                    <td class="px-3 py-2">
                                        <x-filament::input.wrapper class="w-24">
                                            <x-filament::input type="number" min="0" step="0.01" wire:model.blur="studentList.{{ $index }}.theory_marks" />
                                        </x-filament::input.wrapper>
                                    </td>
                                    <td class="px-3 py-2">
                                        <x-filament::input.wrapper class="w-24">
                                            <x-filament::input type="number" min="0" step="0.01" wire:model.blur="studentList.{{ $index }}.practical_marks" />
                                        </x-filament::input.wrapper>
                                    </td>
                                    <td class="px-3 py-2">
                                        <x-filament::input.wrapper class="min-w-36">
                                            <x-filament::input type="text" wire:model.blur="studentList.{{ $index }}.remark" />
                                        </x-filament::input.wrapper>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <x-filament::button icon="heroicon-o-check" wire:click="saveMarks">Save marks</x-filament::button>
                </div>
            </x-filament::section>
        @else
            <x-filament::empty-state icon="heroicon-o-pencil-square" heading="Select an exam, class, section and subject" description="Student marks will appear here once all filters are selected above." />
        @endif
    </div>
</x-filament-panels::page>