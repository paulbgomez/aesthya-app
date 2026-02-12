<?php

use App\Services\Music\MusicService;
use App\Services\Music\YoutubeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates a music track using youtube data', function () {
    config([
        'services.youtube.api_key' => 'test-youtube-key',
    ]);

    Http::fake([
        'www.googleapis.com/youtube/v3/search*' => Http::response([
            'items' => [
                [
                    'id' => [
                        'videoId' => '7lCDEYXw3mM',
                    ],
                    'snippet' => [
                        'title' => 'The Cloud',
                        'channelTitle' => 'Percy Bysshe Shelley',
                        'thumbnails' => [
                            'default' => [
                                'url' => 'https://i.ytimg.com/vi/7lCDEYXw3mM/default.jpg',
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new MusicService(new YoutubeService());

    $track = $service->processTrackData([
        'title' => 'The Cloud',
        'artist' => 'Percy Bysshe Shelley',
    ]);

    expect($track)->not->toBeNull();
    expect($track->title)->toBe('The Cloud');
    expect($track->artist)->toBe('Percy Bysshe Shelley');
    expect(data_get($track->metadata, 'youtube_url'))->toBe('https://www.youtube.com/watch?v=7lCDEYXw3mM');
    expect(data_get($track->metadata, 'video_id'))->toBe('7lCDEYXw3mM');
    expect($track->album_art)->toBe('https://i.ytimg.com/vi/7lCDEYXw3mM/default.jpg');
});
