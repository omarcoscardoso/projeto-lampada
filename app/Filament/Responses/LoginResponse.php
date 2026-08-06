<?php

namespace App\Filament\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            return redirect()->intended(route('app'));
        }

        return redirect()->intended(Filament::getUrl());
    }
}
