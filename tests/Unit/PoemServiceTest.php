<?php

namespace Tests\Unit;

use App\Models\Poem;
use App\Services\Poem\PoemService;
use Illuminate\Support\Facades\Http;

it('fetches poem from poetryDb', function() {
    Http::fake([
        'poetrydb.org/author,title/*' => Http::response([
            [
                'title' => 'The Road Not Taken',
                'author' => 'Robert Frost',
                'lines' => [
                    'Two roads diverged in a yellow wood,',
                    'And sorry I could not travel both',
                    'And be one traveler, long I stood',
                    'And looked down one as far as I could',
                    'To where it bent in the undergrowth;',
                ],
            ],
        ]),
    ]);

    $service = new PoemService;
    $result = $service->processPoemData([
        'name' => 'The Road Not Taken',
        'author' => 'Robert Frost',
    ]);

    expect($result)->toBeInt();
    expect(Poem::query()->where('name', 'The Road Not Taken')->where('author', 'Robert Frost')->exists())->toBeTrue();
});