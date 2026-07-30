<?php

use App\Services\BibleService;
use Illuminate\Support\Facades\Http;

it('fetches verses from the new abibliadigital.api.br endpoint without token', function () {
    Http::fake([
        'https://abibliadigital.api.br/api/verses/nvi/gn/1' => Http::response([
            'book' => [
                'name' => 'Gênesis',
                'version' => 'nvi',
            ],
            'chapter' => [
                'number' => 1,
                'verses' => 31,
            ],
            'verses' => [
                ['number' => 1, 'text' => 'No princípio criou Deus os céus e a terra.'],
                ['number' => 2, 'text' => 'A terra era sem forma e vazia.'],
            ],
        ], 200),
    ]);

    $service = new BibleService;
    $result = $service->getVersesByReference('Gênesis 1');

    expect($result['success'])->toBeTrue();
    expect($result['book_name'])->toBe('Gênesis');
    expect($result['chapters'])->toHaveCount(1);
    expect($result['chapters'][0]['verses'])->toHaveCount(2);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'https://abibliadigital.api.br/api/verses/nvi/gn/1')
            && ! $request->hasHeader('Authorization');
    });
});

it('filters specific verses range correctly when single chapter is requested', function () {
    Http::fake([
        'https://abibliadigital.api.br/api/verses/nvi/gn/1' => Http::response([
            'book' => [
                'name' => 'Gênesis',
                'version' => 'nvi',
            ],
            'chapter' => [
                'number' => 1,
                'verses' => 3,
            ],
            'verses' => [
                ['number' => 1, 'text' => 'Versículo 1'],
                ['number' => 2, 'text' => 'Versículo 2'],
                ['number' => 3, 'text' => 'Versículo 3'],
            ],
        ], 200),
    ]);

    $service = new BibleService;
    $result = $service->getVersesByReference('Gênesis 1:2-3');

    expect($result['success'])->toBeTrue();
    expect($result['chapters'][0]['verses'])->toHaveCount(2);
    expect($result['chapters'][0]['verses'][0]['number'])->toBe(2);
    expect($result['chapters'][0]['verses'][1]['number'])->toBe(3);
});
