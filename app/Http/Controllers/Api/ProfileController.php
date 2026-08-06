<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserGamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Retorna os dados do perfil do usuário autenticado e estatísticas de gamificação.
     */
    public function show(Request $request, UserGamificationService $gamificationService): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $avatar = $user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=128&background=f59e0b&color=ffffff';
        $gamificationData = $gamificationService->getUserGamificationData($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $avatar,
                'is_google' => ! empty($user->google_id),
                'has_password' => ! empty($user->password),
                'created_at_formatted' => $user->created_at ? $user->created_at->format('d/m/Y') : null,
            ],
            'stats' => $gamificationData,
        ]);
    }

    /**
     * Atualiza as informações do perfil do usuário (Nome).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=128&background=f59e0b&color=ffffff',
            ],
        ]);
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Se o usuário já tiver uma senha cadastrada, exige a validação da senha atual
        if (! empty($user->password)) {
            $rules['current_password'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if (! empty($user->password) && ! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'A senha atual informada está incorreta.',
                'errors' => [
                    'current_password' => ['A senha atual informada está incorreta.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso!',
        ]);
    }
}
