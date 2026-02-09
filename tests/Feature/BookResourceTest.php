<?php

use App\Http\Resources\BookResource;
use App\Models\Book;

it('includes cover image in resource', function () {
    $book = Book::create([
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '9780000000001',
        'cover_image' => 'https://example.com/cover.jpg',
    ]);

    $data = (new BookResource($book))->resolve();

    expect($data['coverImage'])->toBe('https://example.com/cover.jpg');
});
