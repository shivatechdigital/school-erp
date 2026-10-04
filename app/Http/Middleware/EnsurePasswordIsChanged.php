<?php

namespace App\Http\Middleware;

use App\Filament\Pages\Auth\ChangePasswordPrompt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Forces staff logged in with the default bulk-import password to the
     * change-password prompt before they can use the rest of the panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password && ! $request->routeIs('filament.admin.pages.change-password-prompt')) {
            return redirect(ChangePasswordPrompt::getUrl());
        }

        return $next($request);
    }
}
