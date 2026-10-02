<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-filament::icon icon="heroicon-o-trophy" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Overall result</div>
                    <div class="mt-1 text-3xl font-bold">{{ number_format((float) $policy->overall_pass_percentage, 2) }}%</div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-primary-500" style="width: {{ min(100, (float) $policy->overall_pass_percentage) }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Minimum overall percentage required to pass an exam.</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                    <x-filament::icon icon="heroicon-o-book-open" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject result</div>
                    <div class="mt-1 text-3xl font-bold">{{ number_format((float) $policy->subject_pass_percentage, 2) }}%</div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-warning-500" style="width: {{ min(100, (float) $policy->subject_pass_percentage) }}%"></div>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Minimum percentage required in each subject. A failed subject makes the overall result fail.</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>