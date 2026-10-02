<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section heading="Overall result">
            <div class="text-3xl font-semibold">{{ number_format((float) $policy->overall_pass_percentage, 2) }}%</div>
            <p class="mt-2 text-sm text-gray-500">Minimum overall percentage required to pass an exam.</p>
        </x-filament::section>
        <x-filament::section heading="Subject result">
            <div class="text-3xl font-semibold">{{ number_format((float) $policy->subject_pass_percentage, 2) }}%</div>
            <p class="mt-2 text-sm text-gray-500">Minimum percentage required in each subject. A failed subject makes the overall result fail.</p>
        </x-filament::section>
    </div>
</x-filament-panels::page>