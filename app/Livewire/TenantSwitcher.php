<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\School;
use Illuminate\Support\Collection;
use Livewire\Component;

class TenantSwitcher extends Component
{
    public ?int $schoolId = null;

    public ?int $branchId = null;

    public ?int $branchOnlyId = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->schoolId = $user?->school_id;
        $this->branchId = $user?->branch_id;
        $this->branchOnlyId = $user?->branch_id;
    }

    public function updatedSchoolId(?string $value): void
    {
        $user = auth()->user();
        $schoolId = filled($value) ? (int) $value : null;

        if ($schoolId && ! $user->canAccessSchool($schoolId)) {
            $this->schoolId = $user->school_id;

            return;
        }

        $user->update(['school_id' => $schoolId, 'branch_id' => null]);
        $this->branchId = null;

        $this->redirect(request()->header('Referer') ?? route('filament.admin.pages.dashboard'), navigate: false);
    }

    public function updatedBranchId(?string $value): void
    {
        $user = auth()->user();
        $branchId = filled($value) ? (int) $value : null;

        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $this->branchId = $user->branch_id;

            return;
        }

        $user->update(['branch_id' => $branchId]);

        $this->redirect(request()->header('Referer') ?? route('filament.admin.pages.dashboard'), navigate: false);
    }

    public function updatedBranchOnlyId(?string $value): void
    {
        $user = auth()->user();
        $branchId = filled($value) ? (int) $value : null;

        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $this->branchId = $user->branch_id;

            return;
        }

        $branch = $branchId ? Branch::query()->withoutGlobalScopes()->find($branchId) : null;

        $user->update(['branch_id' => $branchId, 'school_id' => $branch?->school_id ?? $user->school_id]);

        $this->redirect(request()->header('Referer') ?? route('filament.admin.pages.dashboard'), navigate: false);
    }

    /** @return Collection<int, string> */
    public function getSchoolOptionsProperty(): Collection
    {
        $user = auth()->user();

        if ($user->user_type === 'super_admin') {
            return School::query()->orderBy('name')->pluck('name', 'id');
        }

        return $user->schools()->orderBy('name')->pluck('name', 'id');
    }

    /** @return Collection<int, string> */
    public function getBranchOptionsProperty(): Collection
    {
        $user = auth()->user();

        if (! $this->schoolId) {
            return collect();
        }

        if (in_array($user->user_type, ['super_admin', 'school_admin'], true)) {
            return Branch::query()->withoutGlobalScopes()->where('school_id', $this->schoolId)->orderBy('name')->pluck('name', 'id');
        }

        return $user->branches()->where('school_id', $this->schoolId)->orderBy('name')->pluck('name', 'id');
    }

    /** Flat branch list (across any assigned school) for branch_admin, who has no separate School selector. */
    public function getBranchOnlyOptionsProperty(): Collection
    {
        return auth()->user()->branches()->with('school')->get()
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => "{$branch->school?->name} — {$branch->name}"]);
    }

    public function render()
    {
        return view('livewire.tenant-switcher', [
            'role' => auth()->user()?->user_type,
        ]);
    }
}
