<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;

class ChangePasswordPrompt extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Change Your Password';

    protected string $view = 'filament.pages.auth.change-password-prompt';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        // Skip this page once the one-time password change is done; EnsurePasswordIsChanged middleware is the real gate.
        if (! auth()->user()?->must_change_password) {
            $this->redirect(Filament::getUrl());
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->form([
                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8),
                    TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->same('password'),
                ])
                ->action(function (array $data): void {
                    auth()->user()->update([
                        'password' => $data['password'],
                        'must_change_password' => false,
                    ]);
                })
                ->successRedirectUrl(fn (): string => Filament::getUrl())
                ->successNotificationTitle('Password changed successfully'),

            Action::make('continueDefault')
                ->label('Proceed with Default Password')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Aap default password ("password") ke saath aage badh rahe hain. Isse baad me profile se badal sakte hain.')
                ->action(function (): void {
                    auth()->user()->update(['must_change_password' => false]);
                })
                ->successRedirectUrl(fn (): string => Filament::getUrl()),
        ];
    }
}
