<?php

namespace App\Filament\Auth;

use App\Filament\Pages\Auth\ChangePasswordPrompt;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class RoleAwareLoginResponse implements LoginResponseContract
{
    /**
     * Ignores any stale "intended URL" from a previous, unrelated session so each
     * user always lands on the dashboard their role is actually authorized to view.
     */
    public function toResponse($request): RedirectResponse
    {
        session()->forget('url.intended');

        if (auth()->user()?->must_change_password) {
            return redirect(ChangePasswordPrompt::getUrl());
        }

        return redirect(Filament::getUrl());
    }
}
