<?php

use App\Services\WikidataArtworkService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('successfully finds artwork by title', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => [
                'bindings' => [
                    [
                        'painting' => ['value' => 'http://www.wikidata.org/entity/Q45585'],
                        'paintingLabel' => ['value' => 'The Starry Night'],
                        'creatorLabel' => ['value' => 'Vincent van Gogh'],
                        'image' => ['value' => 'https://example.com/image.jpg'],
                        'inception' => ['value' => '1889-01-01T00:00:00Z'],
                        'collectionLabel' => ['value' => 'Museum of Modern Art'],
                        'materialLabel' => ['value' => 'oil paint'],
                        'movementLabel' => ['value' => 'Post-Impressionism'],
                        'genreLabel' => ['value' => 'landscape painting'],
                        'description' => ['value' => 'painting by Vincent van Gogh'],
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = resolve(WikidataArtworkService::class);
    $result = $service->findArtwork('The Starry Night');

    expect($result)->not->toBeNull();
    expect($result['wikidata_id'])->toBe('Q45585');
    expect($result['title'])->toBe('The Starry Night');
    expect($result['artist'])->toBe('Vincent van Gogh');
    expect($result['image_url'])->toBe('https://example.com/image.jpg');
    expect($result['year'])->toBe(1889);
    expect($result['museum'])->toBe('Museum of Modern Art');
    expect($result['material'])->toBe('oil paint');
    expect($result['movement'])->toBe('Post-Impressionism');
    expect($result['genre'])->toBe('landscape painting');
    expect($result['description'])->toBe('painting by Vincent van Gogh');
});

it('returns null when no results found', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => [
                'bindings' => [],
            ],
        ], 200),
    ]);

    Log::shouldReceive('info')->times(2);

    $service = resolve(WikidataArtworkService::class);
    $result = $service->findArtwork('Nonexistent Painting');

    expect($result)->toBeNull();
});

it('handles wikidata api errors gracefully', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([], 500),
    ]);

    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('error')->andReturn(null);

    $service = resolve(WikidataArtworkService::class);
    $result = $service->findArtwork('Some Painting');

    expect($result)->toBeNull();
});

it('extracts year from inception date', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => [
                'bindings' => [
                    [
                        'painting' => ['value' => 'http://www.wikidata.org/entity/Q12345'],
                        'paintingLabel' => ['value' => 'Test Painting'],
                        'creatorLabel' => ['value' => 'Test Artist'],
                        'inception' => ['value' => '1940-06-15T00:00:00Z'],
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = resolve(WikidataArtworkService::class);
    $result = $service->findArtwork('Test Painting');
    expect($result)->not->toBeNull();
    expect($result['year'])->toBe(1940);
});

it('handles missing optional fields gracefully', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => [
                'bindings' => [
                    [
                        'painting' => ['value' => 'http://www.wikidata.org/entity/Q12345'],
                        'paintingLabel' => ['value' => 'Minimal Data Painting'],
                        'creatorLabel' => ['value' => 'Unknown Artist'],
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = resolve(WikidataArtworkService::class);
    $result = $service->findArtwork('Minimal Data Painting');

    expect($result)->not->toBeNull();
    expect($result['title'])->toBe('Minimal Data Painting');
    expect($result['artist'])->toBe('Unknown Artist');
    expect($result['image_url'])->toBeNull();
    expect($result['year'])->toBeNull();
    expect($result['museum'])->toBeNull();
    expect($result['material'])->toBeNull();
    expect($result['movement'])->toBeNull();
    expect($result['genre'])->toBeNull();
    expect($result['description'])->toBeNull();
});

it('escapes special characters in title for sparql query', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => ['bindings' => []],
        ], 200),
    ]);

    $service = resolve(WikidataArtworkService::class);
    $service->findArtwork('Painting "with" quotes');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'query=') &&
               str_contains(urldecode($request->url()), 'Painting \\"with\\" quotes');
    });
});

it('includes proper user agent in request', function () {
    Http::fake([
        'query.wikidata.org/*' => Http::response([
            'results' => ['bindings' => []],
        ], 200),
    ]);

    Log::shouldReceive('info')->andReturn(null);

    $service = resolve(WikidataArtworkService::class);
    $service->findArtwork('Test');

    Http::assertSent(function ($request) {
        return $request->hasHeader('User-Agent') &&
               str_contains($request->header('User-Agent')[0], 'AesthyaApp');
    });
});
