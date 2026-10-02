<?php

namespace App\Filament\Pages;

use App\Models\GradingPolicy;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GradingPolicySettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'Examination';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Pass / Fail Criteria';

    protected static ?string $title = 'Pass / Fail Criteria';

    protected string $view = 'filament.pages.grading-policy-settings';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->user_type, ['school_admin', 'branch_admin'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editPolicy')
                ->label('Edit criteria')
                ->icon('heroicon-o-pencil-square')
                ->fillForm(fn (): array => $this->currentPolicy()->only([
                    'overall_pass_percentage', 'subject_pass_percentage',
                ]))
                ->form([
                    TextInput::make('overall_pass_percentage')
                        ->label('Minimum overall percentage')
                        ->numeric()->minValue(0)->maxValue(100)->required(),
                    TextInput::make('subject_pass_percentage')
                        ->label('Minimum percentage in each subject')
                        ->numeric()->minValue(0)->maxValue(100)->required(),
                ])
                ->action(function (array $data): void {
                    GradingPolicy::query()->updateOrCreate(
                        ['school_id' => auth()->user()->school_id, 'branch_id' => auth()->user()->branch_id],
                        [
                            'overall_pass_percentage' => $data['overall_pass_percentage'],
                            'subject_pass_percentage' => $data['subject_pass_percentage'],
                            'updated_by' => auth()->id(),
                        ],
                    );

                    Notification::make()->success()->title('Pass/fail criteria updated')->send();
                }),
        ];
    }

    protected function getViewData(): array
    {
        return ['policy' => $this->currentPolicy()];
    }

    private function currentPolicy(): GradingPolicy
    {
        return GradingPolicy::query()->firstOrCreate(
            ['school_id' => auth()->user()->school_id, 'branch_id' => auth()->user()->branch_id],
            ['overall_pass_percentage' => 33, 'subject_pass_percentage' => 33, 'updated_by' => auth()->id()],
        );
    }
}