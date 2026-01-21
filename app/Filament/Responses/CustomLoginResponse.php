<?php

namespace App\Filament\Responses;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class CustomLoginResponse implements \Filament\Auth\Http\Responses\Contracts\LoginResponse
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->isExpired()) {
            auth()->logout();

            Notification::make()
                ->title('Akun Expired')
                ->body('Masa aktif akun Anda telah berakhir. Silakan hubungi admin.')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        return redirect()->intended(Filament::getUrl());
    }
}
