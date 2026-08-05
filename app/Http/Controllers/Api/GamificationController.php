<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserGamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    /**
     * Retorna os dados de gamificação do usuário autenticado.
     */
    public function index(Request $request, UserGamificationService $service): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $data = $service->getUserGamificationData($user);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Marca a leitura da data especificada como concluída.
     */
    public function complete(Request $request, UserGamificationService $service): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $data = $service->markAsCompleted($user, $validated['date']);

        return response()->json([
            'success' => true,
            'message' => 'Leitura concluída com sucesso!',
            'data' => $data,
        ]);
    }
}
