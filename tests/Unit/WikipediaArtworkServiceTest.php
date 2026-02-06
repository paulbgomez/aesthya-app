<?php

namespace Tests\Unit;

use App\Services\Artwork\WikipediaArtworkService;
use Illuminate\Support\Facades\Http;

it('fetches the artwork correctly from Wikipedia', function () {
    Http::fake([
        'en.wikipedia.org/api/rest_v1/page/summary/The_Starry_Night' => Http::response([
            'title' => 'The Starry Night',
            'extract' => 'A famous painting by Vincent van Gogh.',
            'thumbnail' => ['source' => 'https://example.com/image.jpg'],
        ]),
    ]);

    $service = new WikipediaArtworkService();
    $result = $service->fetchArtworkData('The Starry Night');

    $this->assertEquals('A famous painting by Vincent van Gogh.', $result['description']);
    $this->assertEquals('https://example.com/image.jpg', $result['image_url']);
});
