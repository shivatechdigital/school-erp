<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if (! empty($studentList))
            <x-filament::section heading="Student marks" icon="heroicon-o-pencil-square">
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead>
                            <tr class="border-b text-gray-500">
                                <th class="py-2 pe-4">Roll</th>
                                <th class="py-2 pe-4">Student</th>
                                <th class="py-2 pe-4">Section</th>
                                <th class="py-2 pe-4">Theory</th>
                                <th class="py-2 pe-4">Practical</th>
                                <th class="py-2">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($studentList as $index => $student)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 pe-4">{{ $student['roll_no'] }}</td>
                                    <td class="py-2 pe-4 font-medium">{{ $student['name'] }}</td>
                                    <td class="py-2 pe-4">{{ $student['section'] }}</td>
                                    <td class="py-2 pe-4"><input type="number" min="0" step="0.01" wire:model.blur="studentList.{{ $index }}.theory_marks" class="w-24 rounded border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"></td>
                                    <td class="py-2 pe-4"><input type="number" min="0" step="0.01" wire:model.blur="studentList.{{ $index }}.practical_marks" class="w-24 rounded border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"></td>
                                    <td class="py-2"><input type="text" wire:model.blur="studentList.{{ $index }}.remark" class="min-w-36 rounded border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-end">
                    <x-filament::button icon="heroicon-o-check" wire:click="saveMarks">Save marks</x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>