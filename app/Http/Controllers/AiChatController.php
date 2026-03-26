<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Ai\AnonymousAgent;
use Illuminate\Http\JsonResponse;

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
        ]);

        $message = $request->input('message');
        $context = $request->input('devotional_context');

        $systemPrompt = "Você é o 'Assistente Lâmpada', um tutor bíblico empático e sábio.\n"
                      . "Seu objetivo é ajudar o usuário a aprofundar sua leitura devocional.\n"
                      . "Responda sempre com base em princípios bíblicos.\n"
                      . "Seja conciso, acolhedor e encorajador.";

        if ($context) {
            $systemPrompt .= "\n\nO usuário está lendo o seguinte devocional agora. Use-o como contexto para responder:\n" . $context;
        }

        try {
            // Cria um Agente Anônimo com o prompt de sistema e sem histórico de mensagens prévias
            $agent = new AnonymousAgent(
                instructions: $systemPrompt,
                messages: [],
                tools: []
            );

            // Usa a trait Promptable para enviar a pergunta para o provedor padrao (Gemini)
            $response = $agent->prompt($message);

            // O retorno do método prompt() é um objeto do tipo TextResponse ou similar
            $replyText = method_exists($response, 'text') ? $response->text() : 
                        (isset($response->text) ? $response->text : (string) $response);

            return response()->json([
                'response' => $replyText,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocorreu um erro ao processar sua pergunta.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
