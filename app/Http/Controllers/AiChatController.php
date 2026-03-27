<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Responses\AgentResponse;

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
            'history' => 'nullable|array',
        ]);

        $message = $request->input('message');
        $context = $request->input('devotional_context');
        $history = $request->input('history', []);

        // Mapeia o histórico para o formato esperado pelo Agente (user/assistant)
        $mappedHistory = array_map(function ($msg) {
            return [
                'role' => $msg['role'] === 'ai' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }, $history);

        // Limita o histórico das últimas 10 mensagens para não exceder o limite de contexto
        if (count($mappedHistory) > 10) {
            $mappedHistory = array_slice($mappedHistory, -10);
        }

        $systemPrompt = "Você é o 'Assistente Lampião', um tutor bíblico empático e sábio.\n"
            ."Seu objetivo é ajudar o usuário a aprofundar sua leitura devocional.\n"
            ."Responda sempre com base em princípios bíblicos cristão evangélico.\n"
            ."Use uma linguagem acolhedora e encorajadora.\n\n"
            ."Seja sempre curto em suas respostas para facilitar a leitura no celular.\n\n"
            ."Não faça perguntas ao usuário, apenas responda.\n\n"
            ."REGRAS DE FORMATAÇÃO:\n"
            ."- Use quebras de linha frequentes para não criar blocos de texto muito grandes.\n"
            ."- Use listas (bullets) para enumerar pontos de forma didática.\n"
            ."- Use negrito (Markdown: **texto**) para destacar termos importantes.\n"
            ."- Pode usar emojis para ilustrar seus pontos.\n"
            .'- O texto deve ser visualmente limpo e fácil de ler no celular.';

        if ($context) {
            $systemPrompt .= "\n\nO usuário está lendo o seguinte devocional agora. Use-o como contexto para responder:\n".$context;
        }

        try {
            // Cria um Agente Anônimo com o prompt de sistema e o histórico de mensagens prévias
            $agent = new AnonymousAgent(
                instructions: $systemPrompt,
                messages: $mappedHistory,
                tools: []
            );

            // Usa a trait Promptable para enviar a pergunta para o provedor padrao (Gemini)
            $response = $agent->prompt($message);

            /** @var AgentResponse|string $response */
            $replyText = (string) $response;

            return response()->json([
                'response' => $replyText,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocorreu um erro ao processar sua pergunta.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
