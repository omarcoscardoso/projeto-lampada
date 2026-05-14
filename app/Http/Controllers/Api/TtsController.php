<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TtsController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'text' => 'required|string|max:5000', // Limite da API Google TTS Standard
        ]);

        $dateParts = explode('/', $request->input('date'));
        $month = $dateParts[0];
        $day = $dateParts[1];
        $text = $request->input('text');
        $textHash = substr(md5($text), 0, 10); // Hash para diferenciar conteúdos na mesma data

        $path = "audio/{$month}/{$day}/leitura_{$textHash}.mp3";

        try {
            $disk = Storage::disk('gcs');
        } catch (\Exception $e) {
            Log::error('[TTS] Erro ao inicializar disco GCS: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de configuração no servidor de armazenamento (GCS).'
            ], 500);
        }

        try {
            if ($disk->exists($path)) {
                return response()->json([
                    'success' => true,
                    'url' => $disk->url($path),
                    'cached' => true,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('[TTS] Erro ao verificar arquivo no GCS: ' . $e->getMessage());
        }

        $apiKey = env('GOOGLE_TTS_API_KEY');

        if (!$apiKey) {
            Log::error('[TTS] GOOGLE_TTS_API_KEY não configurada no .env');
            return response()->json([
                'success' => false,
                'message' => 'Erro de configuração na API de voz.'
            ], 500);
        }

        $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $apiKey;

        $response = Http::post($url, [
            'input' => [
                'text' => $text,
            ],
            'voice' => [
                'languageCode' => 'pt-BR',
                'name' => 'pt-BR-Standard-E',
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
            ],
        ]);

        if ($response->successful()) {
            $audioContent = base64_decode($response->json('audioContent'));

            try {
                // É CRÍTICO definir o metadata contentType para o navegador reconhecer o MP3
                $disk->put($path, $audioContent, [
                    'visibility' => 'public',
                    'metadata' => [
                        'contentType' => 'audio/mpeg',
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'url' => $disk->url($path),
                    'cached' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('[TTS] Falha ao salvar no bucket: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Erro ao salvar áudio no storage.'], 500);
            }
        }

        Log::error('[TTS] Falha na API do Google: ' . $response->body());

        return response()->json([
            'success' => false,
            'message' => 'Erro ao gerar áudio.',
        ], 500);
    }
}
