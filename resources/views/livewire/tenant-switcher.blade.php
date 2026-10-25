<div class="flex items-center gap-2 px-2">
    @if (in_array($role, ['super_admin', 'school_admin'], true))
        <select
            wire:model.live="schoolId"
            class="fi-select-input block w-40 rounded-lg border-none bg-white/0 text-sm text-gray-950 ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:text-white dark:ring-white/20"
        >
            <option value="">Select School</option>
            @foreach ($this->schoolOptions as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

        <select
            wire:model.live="branchId"
            class="fi-select-input block w-40 rounded-lg border-none bg-white/0 text-sm text-gray-950 ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:text-white dark:ring-white/20"
            @if (! $schoolId) disabled @endif
        >
            <option value="">All Branches</option>
            @foreach ($this->branchOptions as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    @elseif ($role === 'branch_admin' && $this->branchOnlyOptions->count() > 1)
        <select
            wire:model.live="branchOnlyId"
            class="fi-select-input block w-56 rounded-lg border-none bg-white/0 text-sm text-gray-950 ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:text-white dark:ring-white/20"
        >
            <option value="">Select Branch</option>
            @foreach ($this->branchOnlyOptions as $id => $name)
                <option value="{{ $id }}" @selected((int) $id === $branchOnlyId)>{{ $name }}</option>
            @endforeach
        </select>
    @endif
</div>
