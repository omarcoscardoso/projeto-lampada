<?php

namespace App\Http\Controllers;

use App\Ai\LampiaoAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AiChatController extends Controller
{
    /**
     * Process the AI chat request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'devotional_context' => 'nullable|string',
            'conversation_id' => 'nullable|string',
        ]);

        $message = $request->input('message');
        $context = $request->input('devotional_context');
        $conversationId = $request->input('conversation_id');

        // Gera chave de cache baseada na mensagem, contexto e ID da conversa (se houver)
        $cacheKey = 'ai_resp_'.md5($message.$context.$conversationId);

        try {
            $agent = new LampiaoAgent;

            if ($conversationId) {
                // No laravel/ai v0, continue() requer o ID e o participante (user)
                $agent->continue($conversationId, Auth::user() ?? (object) ['id' => 'guest']);
            } elseif (Auth::check()) {
                $agent->forUser(Auth::user());
            }

            $promptMessage = $message;
            if ($context) {
                $promptMessage = "CONTEXTO DO DEVOCIONAL:\n{$context}\n\nPERGUNTA: {$message}";
            }

            // Cache de 1 hora para evitar chamadas duplicadas/idênticas
            $replyText = Cache::remember($cacheKey, now()->addHour(), function () use ($agent, $promptMessage) {
                $response = $agent->prompt($promptMessage);

                return (string) $response;
            });

            return response()->json([
                'response' => $replyText,
                'conversation_id' => $agent->currentConversation(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocorreu um erro ao processar sua pergunta.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
