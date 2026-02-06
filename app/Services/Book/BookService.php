<?php

namespace App\Services\Book;

use App\Models\Book;

class BookService
{
    public function __construct(
        private HardcoverApiService $hardcoverService,
    ) {}

    public function processBookData(string $authorName, string $bookTitle): ?Book
    {
        $query = Book::where('title', $bookTitle);
        
        if ($authorName !== 'Unknown') {
            $query->where('author', $authorName);
        }
        
        $existingBook = $query->first();
        
        if ($existingBook) {
            return $existingBook;
        }

        $response = $this->hardcoverService->fetchBookByAuthorAndTitle($authorName, $bookTitle);
        
        if (isset($response)) {
            $book = Book::create([
                'title' => $response['title'] ?? 'Unknown',
                'author' => $response['author'] ?? 'Unknown',
                'cover_image' => $response['cover_url'] ?? null,
                'metadata' => [
                    'year' => $response['year'] ?? null,
                    'rating' => $response['rating'] ?? null,
                    'pages' => $response['pages'] ?? null,
                    'image_width' => $response['cover_width'] ?? null,
                    'image_height' => $response['cover_height'] ?? null,
                ],
            ]);

            return $book;
        }

        return null;
    }
}       