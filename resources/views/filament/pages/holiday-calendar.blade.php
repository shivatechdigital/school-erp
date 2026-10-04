<x-filament-panels::page>
    <div class="space-y-5">
        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center justify-center gap-2 sm:justify-start">
                <button
                    type="button"
                    wire:click="previousMonth"
                    @disabled(! $canGoPrevious)
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                </button>

                <h2 class="min-w-[9rem] text-center text-lg font-bold text-gray-950 sm:min-w-[11rem] sm:text-xl dark:text-white">
                    {{ $monthLabel }}
                </h2>

                <button
                    type="button"
                    wire:click="nextMonth"
                    @disabled(! $canGoNext)
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                </button>
            </div>

            <div class="flex items-center justify-center gap-3 sm:justify-end">
                @if ($session)
                    <span class="hidden rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 sm:inline-flex dark:bg-primary-500/10 dark:text-primary-300">
                        Session: {{ $session->start_date->format('M Y') }} &ndash; {{ $session->end_date->format('M Y') }}
                    </span>
                @endif

                <x-filament::button color="gray" size="sm" wire:click="goToToday">
                    Today
                </x-filament::button>
            </div>
        </div>

        @if (! $session)
            <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                Academic session set nahi hai, isliye calendar unlimited scroll ho raha hai. Session set karne ke liye
                <a href="{{ \App\Filament\Pages\AcademicSessionSettings::getUrl() }}" class="font-semibold underline">yahan click karein</a>.
            </div>
        @endif

        {{-- Calendar --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-white/5">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $label)
                    <div class="px-1 py-2 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-400 sm:text-xs dark:text-gray-500">
                        {{ $label }}
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-px bg-gray-100 dark:bg-white/5">
                @for ($i = 0; $i < $leadingBlankDays; $i++)
                    <div class="min-h-[64px] bg-white sm:min-h-[90px] md:min-h-[108px] dark:bg-gray-900"></div>
                @endfor

                @foreach ($days as $day)
                    <button
                        type="button"
                        wire:click="mountAction('manageHoliday', { date: '{{ $day['date']->toDateString() }}' })"
                        title="{{ $day['holiday']?->title }}"
                        @class([
                            'group relative flex min-h-[64px] flex-col items-start gap-1 bg-white p-1.5 text-left transition hover:bg-primary-50 sm:min-h-[90px] sm:p-2 md:min-h-[108px] dark:bg-gray-900 dark:hover:bg-primary-500/10',
                            'bg-gray-50 dark:bg-white/[0.03]' => $day['isWeekend'] && ! $day['holiday'],
                            'bg-amber-50 dark:bg-amber-500/10' => $day['holiday'],
                        ])
                    >
                        <span
                            @class([
                                'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold sm:text-sm',
                                'bg-primary-600 text-white' => $day['isToday'],
                                'text-gray-700 dark:text-gray-300' => ! $day['isToday'],
                            ])
                        >
                            {{ $day['date']->day }}
                        </span>

                        @if ($day['holiday'])
                            <span class="w-full truncate rounded-md bg-amber-500 px-1.5 py-0.5 text-[10px] font-medium text-white sm:text-xs">
                                {{ $day['holiday']->title }}
                            </span>
                        @endif

                        <x-filament::icon
                            icon="heroicon-m-plus"
                            class="absolute right-1 top-1 h-3.5 w-3.5 text-gray-300 opacity-0 transition group-hover:opacity-100 sm:h-4 sm:w-4 dark:text-gray-600"
                        />
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-primary-600"></span> Today
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-white/20"></span> Weekend
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Holiday
            </span>
            <span class="text-gray-400 dark:text-gray-500">Kisi bhi date par click karke holiday add/edit/remove karein.</span>
        </div>
    </div>
</x-filament-panels::page>
