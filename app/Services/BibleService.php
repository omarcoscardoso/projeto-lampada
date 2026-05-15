<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BibleService
{
    private string $baseUrl;

    private string $token;

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
        $this->baseUrl = config('services.abibliadigital.base_url', 'https://www.abibliadigital.com.br/api');
        $this->token = config('services.abibliadigital.token', '');
        $this->version = config('services.abibliadigital.default_version', 'nvi');
    }

    /**
     * Get verses by reference string like "Gênesis 1:1-5" or "Josué 22/23/24"
     */
    public function getVersesByReference(string $reference): array
    {
        $cacheKey = 'bible_ref_'.md5(Str::lower(trim($reference)));

        // Never cache failures so we can debug and retry
        $cached = Cache::get($cacheKey);
        if ($cached && ($cached['success'] ?? false)) {
            return $cached;
        }

        $parsed = $this->parseReference($reference);

        if (! $parsed) {
            return ['success' => false, 'message' => "Referência inválida: {$reference}", 'chapters' => []];
        }

        $allResults = [];
        $bookName = '';
        $finalVersion = '';
        $allDebug = [];

        foreach ($parsed['chapters'] as $chapter) {
            $chapterData = $this->fetchChapterWithRetry($parsed['book'], $chapter, $parsed);
            if ($chapterData['success']) {
                $allResults[] = $chapterData;
                $bookName = $chapterData['book_name'];
                $finalVersion = $chapterData['version'];
            } else {
                $allDebug = array_merge($allDebug, $chapterData['debug'] ?? []);
            }
        }

        if (empty($allResults)) {
            return [
                'success' => false,
                'message' => "Não foi possível carregar os textos para {$reference}",
                'chapters' => [],
                'debug_info' => $allDebug,
            ];
        }

        $result = [
            'success' => true,
            'book_name' => $bookName,
            'version' => $finalVersion,
            'chapters' => $allResults,
        ];

        Cache::put($cacheKey, $result, now()->addDays(30));

        return $result;
    }

    private function fetchChapterWithRetry(string $book, int $chapter, array $parsed): array
    {
        $versionsToTry = array_unique([$this->version, 'nvi', 'acf', 'aa', 'ra']);
        $debugAttempts = [];

        foreach ($versionsToTry as $currentVersion) {
            $endpoint = "{$this->baseUrl}/verses/{$currentVersion}/{$book}/{$chapter}";
            $request = Http::acceptJson()->withUserAgent('LampadaApp/1.0')->timeout(15);

            if ($this->token) {
                $request = $request->withToken($this->token);
            }

            try {
                $response = $request->get($endpoint);
            } catch (ConnectionException $e) {
                $debugAttempts[] = [
                    'version' => $currentVersion,
                    'endpoint' => $endpoint,
                    'error' => 'connection_exception: '.$e->getMessage(),
                ];
                Log::warning('BibleService: connection error', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            $attempt = [
                'version' => $currentVersion,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'has_token' => (bool) $this->token,
            ];

            if (! $response->successful()) {
                $attempt['response_body'] = substr($response->body(), 0, 300);
                Log::warning('BibleService: failed attempt', $attempt);
                $debugAttempts[] = $attempt;

                continue;
            }

            $data = $response->json();
            $verses = $data['verses'] ?? [];

            // Individual verse filtering only applies if single chapter
            if (count($parsed['chapters']) === 1 && $parsed['start_verse']) {
                $verses = array_values(array_filter($verses, function ($v) use ($parsed) {
                    $num = (int) $v['number'];

                    return $parsed['end_verse'] ? ($num >= $parsed['start_verse'] && $num <= $parsed['end_verse']) : ($num === $parsed['start_verse']);
                }));
            }

            return [
                'success' => true,
                'number' => $chapter,
                'book_name' => $data['book']['name'] ?? '',
                'version' => $currentVersion,
                'verses' => $verses,
            ];
        }

        Log::error('BibleService: all versions failed', [
            'book' => $book,
            'chapter' => $chapter,
            'attempts' => $debugAttempts,
        ]);

        return ['success' => false, 'debug' => $debugAttempts];
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
        ];
    }
}
