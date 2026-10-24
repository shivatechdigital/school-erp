<x-filament-panels::page>
    <div class="space-y-5">
        <x-filament::section heading="How it works">
            <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-400">
                <li>Ek class chuno — uske saare active sections ke liye timetable ek saath ban jayega.</li>
                <li>Har subject utni hi periods/week leta hai jitni Class &rarr; Subjects mein set hai, aur wahi teacher assign hota hai.</li>
                <li>Koi bhi teacher school mein ek hi din-period pe sirf ek jagah hi padhayega — clash automatically avoid ho jata hai.</li>
                <li>Agar kisi subject ke saare periods free slot na milne ki wajah se nahi ban paate, toh result mein bata diya jayega.</li>
            </ul>
        </x-filament::section>

        @if ($lastPlaced !== null)
            <x-filament::section heading="Last result: {{ $lastClassName }}">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-gray-950 dark:text-white">{{ $lastPlaced }}</span> periods placed successfully.
                </p>

                @if (count($lastNotes))
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-amber-700 dark:text-amber-400">
                        @foreach ($lastNotes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                @endif

                <a href="{{ $timetableUrl }}" class="mt-4 inline-flex text-sm font-semibold text-primary-600 underline dark:text-primary-400">
                    View generated timetable &rarr;
                </a>
            </x-filament::section>
        @else
            <x-filament::empty-state
                icon="heroicon-o-sparkles"
                heading="No timetable generated yet"
                description="Upar 'Generate Timetable' button se shuru karein."
            />
        @endif
    </div>
</x-filament-panels::page>
