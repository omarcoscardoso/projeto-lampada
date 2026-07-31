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
        $executionTrace = [];
        $executionTrace[] = 'Iniciando processo às '.now()->toDateTimeString();

        $request->validate([
            'date' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'blocks' => 'required|array',
            'blocks.*.testament' => 'required|in:old,new',
            'blocks.*.book_name' => 'required|string',
            'blocks.*.book_abbrev' => 'required|string',
            'blocks.*.chapter' => 'required|integer',
            'blocks.*.start_verse' => 'nullable|integer',
            'blocks.*.end_verse' => 'nullable|integer',
            'blocks.*.text' => 'required|string|max:500000',
        ]);

        $blocksInput = $request->input('blocks');
        $dateInput = $request->input('date');
        $executionTrace[] = 'Blocos recebidos: '.count($blocksInput);

        $dateParts = explode('/', $dateInput);
        $month = $dateParts[0];
        $day = $dateParts[1];

        try {
            $bucketName = config('filesystems.disks.gcs.bucket');
            $executionTrace[] = 'Bucket configurado: '.($bucketName ?: 'NULO');
            $disk = Storage::disk('gcs');
            $executionTrace[] = 'Disco GCS inicializado com driver: '.config('filesystems.disks.gcs.driver');
        } catch (\Exception $e) {
            Log::error('[TTS] Erro ao inicializar disco GCS: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de configuração no servidor de armazenamento (GCS).',
            ], 500);
        }

        $apiKey = config('services.google.tts_key') ?? env('GOOGLE_TTS_API_KEY');
        $executionTrace[] = 'Google API Key carregada: '.($apiKey ? 'SIM' : 'NÃO');

        $audioUrls = [];
        $anyGenerated = false;

        foreach ($blocksInput as $blockIndex => $block) {
            $testament = $block['testament'];
            $bookAbbrev = $block['book_abbrev'];
            $chapter = $block['chapter'];
            $startVerse = $block['start_verse'] ?? null;
            $endVerse = $block['end_verse'] ?? null;

            $fullText = trim($block['text']);
            $fullText = str_replace(["\r\n", "\r"], "\n", $fullText);
            $fullText = preg_replace("/\n{2,}/", "\n\n", $fullText);

            $textChunks = $this->chunkText($fullText, self::GOOGLE_TTS_MAX_CHARS);
            $executionTrace[] = "Bloco {$blockIndex} ({$bookAbbrev} {$chapter}) dividido em ".count($textChunks).' chunks.';

            foreach ($textChunks as $index => $chunk) {
                // Formatting the base name
                if ($testament === 'new' && $startVerse !== null && $endVerse !== null) {
                    $baseName = "{$bookAbbrev}_{$chapter}_{$startVerse}-{$endVerse}";
                } else {
                    $baseName = "{$bookAbbrev}_{$chapter}";
                }

                $suffix = count($textChunks) > 1 ? "_{$index}" : '';
                $chunkPath = "audio/{$month}/{$day}/{$baseName}{$suffix}.mp3";

                // Verifica se o áudio do chunk individual já existe
                try {
                    $exists = $disk->exists($chunkPath);
                    $executionTrace[] = "Verificando chunk {$chunkPath}: ".($exists ? 'EXISTE' : 'NÃO EXISTE');

                    if ($exists) {
                        $url = $disk->url($chunkPath);
                        $audioUrls[] = $url;

                        continue; // Pula a geração para este chunk
                    }
                } catch (\Exception $e) {
                    $executionTrace[] = "Erro ao verificar GCS para {$chunkPath}: ".$e->getMessage();
                }

                $url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key='.$apiKey;

                try {
                    $response = Http::post($url, [
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
                    $executionTrace[] = 'Erro de rede Google API: '.$e->getMessage();

                    return response()->json([
                        'success' => false,
                        'message' => 'Falha de comunicação com o serviço de voz externo.',
                        'trace' => $executionTrace,
                    ], 500);
                }

                if ($response->successful()) {
                    $audioContent = base64_decode($response->json('audioContent') ?? '');
                    $executionTrace[] = "Google TTS OK. Audio para {$chunkPath} gerado: ".strlen($audioContent).' bytes.';
                    $anyGenerated = true;

                    try {
                        $putResult = $disk->put($chunkPath, $audioContent, [
                            'visibility' => 'public',
                            'mimetype' => 'audio/mpeg',
                            'metadata' => ['contentType' => 'audio/mpeg'],
                        ]);

                        $executionTrace[] = "Resultado do put no GCS para {$chunkPath}: ".($putResult ? 'SUCESSO' : 'FALHA');

                        $url = $disk->url($chunkPath);
                        $audioUrls[] = $url;
                    } catch (\Exception $e) {
                        $executionTrace[] = "Exceção ao salvar no GCS para {$chunkPath}: ".$e->getMessage();

                        return response()->json(['success' => false, 'message' => 'Erro ao salvar no storage.', 'trace' => $executionTrace], 500);
                    }
                } else {
                    $executionTrace[] = 'Erro API Google Status '.$response->status().': '.$response->body();

                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao gerar áudio para um dos trechos.',
                    ], $response->status() ?: 500);
                }
            }
        }

        return response()->json([
            'success' => true,
            'urls' => $audioUrls,
            'cached' => ! $anyGenerated,
            'debug' => [
                'trace' => $executionTrace,
                'final_bucket' => config('filesystems.disks.gcs.bucket'),
            ],
        ]);
    }

    /**
     * Divide um texto em chunks, tentando quebrar em limites de frases.
     *
     * @param  string  $text  O texto de entrada.
     * @param  int  $maxChars  O número máximo de caracteres por chunk.
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
