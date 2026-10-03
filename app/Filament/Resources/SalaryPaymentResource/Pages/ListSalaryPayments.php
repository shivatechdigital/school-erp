<?php

namespace App\Filament\Resources\SalaryPaymentResource\Pages;

use App\Filament\Resources\SalaryPaymentResource;
use App\Filament\Resources\Staff\StaffResource;
use App\Models\SalaryPayment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSalaryPayments extends ListRecords
{
    protected static string $resource = SalaryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateForThisMonth')
                ->label('Generate for This Month')
                ->icon('heroicon-o-document-plus')
                ->requiresConfirmation()
                ->modalDescription('Creates a pending salary payment entry (using each staff member\'s current salary) for this month, for any staff who don\'t already have one.')
                ->action(function (): void {
                    $user = auth()->user();
                    $periodMonth = now()->startOfMonth()->toDateString();

                    $staff = User::query()
                        ->whereIn('user_type', StaffResource::STAFF_TYPES)
                        ->where('status', 'active')
                        ->where('school_id', $user->school_id)
                        ->when($user->branch_id, fn (Builder $query, $branchId) => $query->where('branch_id', $branchId))
                        ->get();

                    $created = 0;

                    foreach ($staff as $member) {
                        $exists = SalaryPayment::query()
                            ->where('user_id', $member->id)
                            ->where('period_month', $periodMonth)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        SalaryPayment::query()->create([
                            'school_id' => $member->school_id,
                            'branch_id' => $member->branch_id,
                            'user_id' => $member->id,
                            'period_month' => $periodMonth,
                            'amount' => $member->salary ?? 0,
                            'status' => 'pending',
                        ]);
                        $created++;
                    }

                    Notification::make()->success()->title("{$created} salary payment(s) generated")->send();
                }),
        ];
    }
}
