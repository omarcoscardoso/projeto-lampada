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

it('resolves Filemon and Filemom references correctly including single chapter books', function (string $reference) {
    Http::fake([
        'https://abibliadigital.api.br/api/verses/nvi/fm/1' => Http::response([
            'book' => [
                'name' => 'Filemom',
                'version' => 'nvi',
            ],
            'chapter' => [
                'number' => 1,
                'verses' => 25,
            ],
            'verses' => [
                ['number' => 1, 'text' => 'Paulo, prisioneiro de Cristo Jesus...'],
                ['number' => 2, 'text' => 'à irmã Áfia...'],
            ],
        ], 200),
    ]);

    $service = new BibleService;
    $result = $service->getVersesByReference($reference);

    expect($result['success'])->toBeTrue();
    expect($result['book_name'])->toBe('Filemom');
    expect($result['book_abbrev'])->toBe('fm');
    expect($result['chapters'])->toHaveCount(1);
    expect($result['chapters'][0]['verses'])->toHaveCount(2);
})->with([
    'Filemon 1',
    'Filemon',
    'Filemom 1',
    'Filemom',
]);

it('resolves singular book names and abbreviations like Salmo and Mt', function () {
    Http::fake([
        'https://abibliadigital.api.br/api/verses/nvi/sl/23' => Http::response([
            'book' => ['name' => 'Salmos', 'version' => 'nvi'],
            'chapter' => ['number' => 23, 'verses' => 6],
            'verses' => [['number' => 1, 'text' => 'O Senhor é o meu pastor...']],
        ], 200),
        'https://abibliadigital.api.br/api/verses/nvi/mt/22' => Http::response([
            'book' => ['name' => 'Mateus', 'version' => 'nvi'],
            'chapter' => ['number' => 22, 'verses' => 46],
            'verses' => [
                ['number' => 1, 'text' => 'Jesus lhes falou novamente por parábolas...'],
                ['number' => 2, 'text' => 'O Reino dos céus é semelhante...'],
            ],
        ], 200),
    ]);

    $service = new BibleService;

    $salmoResult = $service->getVersesByReference('Salmo 23');
    expect($salmoResult['success'])->toBeTrue();
    expect($salmoResult['book_abbrev'])->toBe('sl');

    $mtResult = $service->getVersesByReference('Mt 22:1-2');
    expect($mtResult['success'])->toBeTrue();
    expect($mtResult['book_abbrev'])->toBe('mt');
    expect($mtResult['chapters'][0]['verses'])->toHaveCount(2);
});
