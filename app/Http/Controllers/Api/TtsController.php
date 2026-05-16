<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TtsController extends Controller
{
    private const GOOGLE_TTS_MAX_CHARS = 4800; // Google supports up to 5000 bytes, 4800 chars is safer

    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'text' => 'required|string|max:500000', // Aumentado para permitir textos mais longos, o chunking cuidará do limite da API
        ]);

        $dateParts = explode('/', $request->input('date'));
        $month = $dateParts[0];
        $day = $dateParts[1];

        // Normalização: Remove espaços extras e padroniza quebras de linha para evitar hashes diferentes por bobeira
        $fullText = trim($request->input('text'));
        $fullText = str_replace(["\r\n", "\r"], "\n", $fullText);
        $fullText = preg_replace("/\n{2,}/", "\n\n", $fullText); // Opcional: evita múltiplas quebras seguidas

        $fullTextHash = substr(md5($fullText), 0, 16);

        try {
            $disk = Storage::disk('gcs');
        } catch (\Exception $e) {
            Log::error('[TTS] Erro ao inicializar disco GCS: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de configuração no servidor de armazenamento (GCS).'
            ], 500);
        }

        // Busca do config/services.php ou fallback para env() se não estiver cacheado
        $apiKey = config('services.google.tts_key') ?? env('GOOGLE_TTS_API_KEY');

        if (!$apiKey) {
            Log::error('[TTS] Chave da API do Google não encontrada. Certifique-se de usar config:cache corretamente.');
            return response()->json([
                'success' => false,
                'message' => 'Erro de configuração na API de voz.'
            ], 500);
        }

        $textChunks = $this->chunkText($fullText, self::GOOGLE_TTS_MAX_CHARS);
        $audioUrls = [];
        $anyGenerated = false;

        foreach ($textChunks as $index => $chunk) {
            // Geramos o hash do conteúdo do chunk para garantir unicidade
            $chunkHash = md5($chunk);

            // Organizamos os pedaços em uma subpasta 'chunks' para manter o diretório da data limpo
            $chunkPath = "audio/{$month}/{$day}/chunks/{$chunkHash}.mp3";

            // Verifica se o áudio do chunk individual já existe
            try {
                if ($disk->exists($chunkPath)) {
                    Log::debug("[TTS] Chunk {$index} encontrado no cache.");
                    $audioUrls[] = $disk->url($chunkPath);
                    continue; // Pula a geração para este chunk
                }
            } catch (\Exception $e) {
                Log::warning('[TTS] Erro ao verificar chunk de áudio no GCS: ' . $e->getMessage());
            }

            $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . $apiKey;

            try {
                // Adicionado timeout e tratamento de exceção de conexão
                $response = Http::timeout(30)->post($url, [
                    'input' => [
                        'text' => $chunk,
                    ],
                    'voice' => [
                        'languageCode' => 'pt-BR',
                        'name' => 'pt-BR-Standard-E',
                    ],
                    'audioConfig' => [
                        'audioEncoding' => 'MP3',
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('[TTS] Erro de rede ao conectar com Google API: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Falha de comunicação com o serviço de voz externo.',
                ], 500);
            }

            if ($response->successful()) {
                $audioContent = base64_decode($response->json('audioContent'));
                $anyGenerated = true;

                try {
                    // Importante: mimetype e ContentType garantem que o navegador não dê 404/NotSupported
                    $disk->put($chunkPath, $audioContent, [
                        'visibility' => 'public',
                        'mimetype' => 'audio/mpeg',
                        'metadata' => ['contentType' => 'audio/mpeg']
                    ]);
                    $audioUrls[] = $disk->url($chunkPath);
                } catch (\Exception $e) {
                    Log::error('[TTS] Falha ao salvar chunk de áudio no bucket: ' . $e->getMessage());
                    return response()->json(['success' => false, 'message' => 'Erro ao salvar áudio no storage.'], 500);
                }
            } else {
                Log::error('[TTS] Falha na API do Google para chunk ' . $index . ': ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao gerar áudio para um dos trechos.',
                ], 500);
            }
        }

        Log::info("[TTS] Processamento concluído. Chunks totais: " . count($audioUrls) . ($anyGenerated ? " (Novos gerados)" : " (Todos do cache)"));

        return response()->json([
            'success' => true,
            'urls' => $audioUrls,
            'cached' => !$anyGenerated,
        ]);
    }

    /**
     * Divide um texto em chunks, tentando quebrar em limites de frases.
     *
     * @param string $text O texto de entrada.
     * @param int $maxChars O número máximo de caracteres por chunk.
     * @return array Um array de chunks de texto.
     */
    private function chunkText(string $text, int $maxChars): array
    {
        $chunks = [];
        $currentText = $text;

        while (mb_strlen($currentText) > 0) {
            if (mb_strlen($currentText) <= $maxChars) {
                $chunks[] = $currentText;
                break;
            }

            $chunkCandidate = mb_substr($currentText, 0, $maxChars);

            // Tenta encontrar um final de frase dentro do chunk candidato
            $splitPos = false;
            // Procura por finais de frase comuns, priorizando os mais próximos do final do chunk
            $sentenceEndings = ['.', '?', '!'];
            foreach ($sentenceEndings as $ending) {
                $pos = mb_strrpos($chunkCandidate, $ending);
                // Garante que a quebra não seja muito cedo no chunk (ex: pelo menos 70% de maxChars)
                if ($pos !== false && $pos >= $maxChars * 0.7) {
                    $splitPos = $pos;
                    break;
                }
            }

            if ($splitPos !== false) {
                $chunk = mb_substr($currentText, 0, $splitPos + 1);
                $currentText = mb_substr($currentText, $splitPos + 1);
            } else {
                // Fallback: se nenhum limite de frase bom for encontrado, quebra em maxChars
                $chunk = mb_substr($currentText, 0, $maxChars);
                $currentText = mb_substr($currentText, $maxChars);
            }
            $chunks[] = trim($chunk);
        }

        return array_filter($chunks); // Remove quaisquer chunks vazios que possam resultar do trim
    }
}
