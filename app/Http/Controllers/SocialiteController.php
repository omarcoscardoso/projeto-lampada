<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/admin/login');
        }

        // Tenta encontrar pelo google_id primeiro
        $user = User::where('google_id', $googleUser->id)->first();

        if (! $user) {
            // Se não encontrar, tenta pelo email (para vincular conta já existente)
            $user = User::where('email', $googleUser->email)->first();
        }

        if ($user) {
            // Atualiza os dados do usuário existente
            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
            ]);
        } else {
            // Cria um novo usuário se não existir nada
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        // Atribuir papel padrão se não tiver nenhum
        if (! $user->roles()->exists()) {
            $user->assignRole('panel_user');
        }

        Auth::login($user);

        return redirect()->intended(route('app'));
    }
}
