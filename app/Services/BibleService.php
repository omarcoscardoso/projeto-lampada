<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BibleService
{
    private string $baseUrl;

    private string $version;

    private array $bookMap = [
        'gênesis' => 'gn',
        'exodo' => 'ex',
        'êxodo' => 'ex',
        'levítico' => 'lv',
        'levitico' => 'lv',
        'números' => 'nm',
        'numeros' => 'nm',
        'deuteronômio' => 'dt',
        'deuteronomio' => 'dt',
        'josué' => 'js',
        'josue' => 'js',
        'juízes' => 'jz',
        'juizes' => 'jz',
        'rute' => 'rt',
        '1 samuel' => '1sm',
        '2 samuel' => '2sm',
        '1 reis' => '1rs',
        '2 reis' => '2rs',
        '1 crônicas' => '1cr',
        '1 cronicas' => '1cr',
        '2 crônicas' => '2cr',
        '2 cronicas' => '2cr',
        'esdras' => 'ed',
        'neemias' => 'ne',
        'ester' => 'et',
        'jó' => 'job',
        'salmos' => 'sl',
        'provérbios' => 'pv',
        'proverbios' => 'pv',
        'eclesiastes' => 'ec',
        'cantares' => 'ct',
        'isaías' => 'is',
        'isaias' => 'is',
        'jeremias' => 'jr',
        'lamentações' => 'lm',
        'lamentacoes' => 'lm',
        'ezequiel' => 'ez',
        'daniel' => 'dn',
        'oseias' => 'os',
        'oséias' => 'os',
        'joel' => 'jl',
        'amós' => 'am',
        'amos' => 'am',
        'obadias' => 'ob',
        'jonas' => 'jn',
        'miqueias' => 'mq',
        'miquéias' => 'mq',
        'naum' => 'na',
        'habacuque' => 'hc',
        'sofonias' => 'sf',
        'ageu' => 'ag',
        'zacarias' => 'zc',
        'malaquias' => 'ml',
        'mateus' => 'mt',
        'marcos' => 'mc',
        'lucas' => 'lc',
        'joão' => 'jo',
        'atos' => 'at',
        'romanos' => 'rm',
        '1 coríntios' => '1co',
        '1 corintios' => '1co',
        '2 coríntios' => '2co',
        '2 corintios' => '2co',
        'gálatas' => 'gl',
        'galatas' => 'gl',
        'efésios' => 'ef',
        'efesios' => 'ef',
        'filipenses' => 'fp',
        'colossenses' => 'cl',
        '1 tessalonicenses' => '1ts',
        '2 tessalonicenses' => '2ts',
        '1 timóteo' => '1tm',
        '1 timoteo' => '1tm',
        '2 timóteo' => '2tm',
        '2 timoteo' => '2tm',
        'tito' => 'tt',
        'filemom' => 'fm',
        'hebreus' => 'hb',
        'tiago' => 'tg',
        '1 pedro' => '1pe',
        '2 pedro' => '2pe',
        '1 joão' => '1jo',
        '1 joao' => '1jo',
        '2 joão' => '2jo',
        '2 joao' => '2jo',
        '3 joão' => '3jo',
        '3 joao' => '3jo',
        'judas' => 'jd',
        'apocalipse' => 'ap',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.abibliadigital.base_url', 'https://www.abibliadigital.api.br/api');
        $this->version = config('services.abibliadigital.default_version', 'nvi');
    }

    /**
     * Get verses by reference string like "Gênesis 1:1-5" or "Josué 22/23/24"
     */
    public function getVersesByReference(string $reference): array
    {
        $cacheKey = 'bible_ref_'.md5(Str::lower(trim($reference)));

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $parsed = $this->parseReference($reference);

        if (! $parsed) {
            return ['success' => false, 'message' => "Referência inválida: {$reference}", 'chapters' => []];
        }

        $allResults = [];
        $bookName = '';
        $finalVersion = '';

        foreach ($parsed['chapters'] as $chapter) {
            $chapterData = $this->fetchChapterWithRetry($parsed['book'], $chapter, $parsed);
            if ($chapterData['success']) {
                $allResults[] = $chapterData;
                $bookName = $chapterData['book_name'];
                $finalVersion = $chapterData['version'];
            }
        }

        if (empty($allResults)) {
            Log::error("[BibleService] Falha total ao buscar referência em produção: {$reference}. Verifique conectividade com a API externa ou limite de taxa (Rate Limit).");

            return ['success' => false, 'message' => "Não foi possível carregar os textos para {$reference}", 'chapters' => []];
        }

        $result = [
            'success' => true,
            'book_name' => $bookName,
            'book_abbrev' => $parsed['book'] ?? '',
            'version' => $finalVersion,
            'chapters' => $allResults,
        ];

        Cache::put($cacheKey, $result, now()->addDays(30));

        return $result;
    }

    private function fetchChapterWithRetry(string $book, int $chapter, array $parsed): array
    {
        $versionsToTry = array_unique([$this->version, 'nvi', 'acf', 'aa', 'ra', 'kjv']); // Added KJV as another common fallback

        foreach ($versionsToTry as $currentVersion) {
            $endpoint = "{$this->baseUrl}/verses/{$currentVersion}/{$book}/{$chapter}";
            $originalReference = $parsed['original_reference'] ?? 'unknown';

            try {
                Log::debug("[BibleService] Tentando buscar {$book} {$chapter} ({$currentVersion}) para referência original: {$originalReference}");
                $response = Http::acceptJson()->withUserAgent('LampadaApp/1.0')->get($endpoint);
            } catch (\Exception $e) {
                Log::error('[BibleService] Erro de conexão ao acessar a API: '.$e->getMessage());

                continue;
            }
            if ($response->successful()) {
                $data = $response->json();
                $verses = $data['verses'] ?? [];

                // Individual verse filtering only applies if single chapter
                if (count($parsed['chapters']) === 1 && $parsed['start_verse']) {
                    $originalCount = count($verses);
                    $verses = array_values(array_filter($verses, function ($v) use ($parsed) {
                        $num = (int) $v['number'];

                        return $parsed['end_verse'] ? ($num >= $parsed['start_verse'] && $num <= $parsed['end_verse']) : ($num === $parsed['start_verse']);
                    }));
                    Log::debug("[BibleService] Verses filtered for {$book} {$chapter} (ref: {$originalReference}): ".count($verses).' out of '.$originalCount.' original verses.');
                }
                if (empty($verses)) {
                    Log::warning("[BibleService] Nenhuma verso encontrado após filtragem para {$book} {$chapter} ({$currentVersion}) para referência original: {$originalReference}. API response successful, but verses array is empty or filtered out.");
                }

                return [
                    'success' => true,
                    'number' => $chapter,
                    'book_name' => $data['book']['name'] ?? '',
                    'version' => $currentVersion,
                    'verses' => $verses,
                ];
            } else {
                Log::warning('[BibleService] Falha na requisição', [
                    'url' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }

        return ['success' => false];
    }

    private function parseReference(string $reference): ?array
    {
        $reference = trim($reference);

        // 1. Extract Book Name (everything until first digit)
        if (! preg_match('/^([^\d\:\/]+)/u', $reference, $bookMatches)) {
            // Check for books starting with number like "1 João"
            if (! preg_match('/^(\d\s*[^\d\:\/]+)/u', $reference, $bookMatches)) {
                return null;
            }
        }

        $bookPart = $bookMatches[1];
        $bookNameNormalized = trim(Str::lower($bookPart));
        $bookNameNormalized = preg_replace('/(\d)\s*([a-zâêîôûáéíóúãõç])/u', '$1 $2', $bookNameNormalized);

        $bookAbbrev = $this->bookMap[$bookNameNormalized] ?? null;
        if (! $bookAbbrev) {
            foreach ($this->bookMap as $name => $abbrev) {
                if (Str::startsWith($bookNameNormalized, $name)) {
                    $bookAbbrev = $abbrev;
                    break;
                }
            }
        }
        if (! $bookAbbrev) {
            return null;
        }

        // 2. Extract the rest
        $rest = trim(substr($reference, strlen($bookPart)));

        // Handle chapters separated by slashes (e.g. 22/23/24)
        $chapters = [];
        if (preg_match('/^(\d+(?:\/\d+)*)/', $rest, $chapterMatches)) {
            $chapters = explode('/', $chapterMatches[1]);
        } else {
            return null;
        }

        // Optional Verses (only if one chapter is specified)
        $startVerse = null;
        $endVerse = null;
        if (count($chapters) === 1 && preg_match('/:(\d+)(?:-(\d+))?/', $rest, $verseMatches)) {
            $startVerse = (int) $verseMatches[1];
            $endVerse = isset($verseMatches[2]) ? (int) $verseMatches[2] : null;
        }

        return [
            'book' => $bookAbbrev,
            'chapters' => array_map('intval', $chapters),
            'start_verse' => $startVerse,
            'end_verse' => $endVerse,
            'original_reference' => $reference,
        ];
    }
}
