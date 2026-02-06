<?php

namespace App\Services\Book;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HardcoverApiService
{
    private const GRAPHQL_ENDPOINT = 'https://api.hardcover.app/v1/graphql';
    private const TIMEOUT = 10;

    private function getBookByAuthorAndTitleQuery(): string
    {
        return <<<'GRAPHQL'
            query GetAuthorContributions($authorName: String!, $bookTitle: String!) {
                contributions(
                    where: {
                        author: { name: { _eq: $authorName } }
                        _and: { book: { title: { _eq: $bookTitle } } }
                    }
                ) {
                    id
                    book {
                        id
                        title
                        release_year
                        rating
                        pages
                        image {
                            id
                            width
                            height
                            url
                        }
                    }
                    created_at
                }
            }
        GRAPHQL;
    }

    private function executeGraphQL(string $query, array $variables = []): ?array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . env('HARDCOVER_API_KEY'),
                ])
                ->post(self::GRAPHQL_ENDPOINT, [
                    'query' => $query,
                    'variables' => $variables,
                ]);

            if ($response->failed()) {
                throw new \Exception('GraphQL request failed: ' . $response->status());
            }

            $data = $response->json();
            
            if (isset($data['errors'])) {
                Log::error('GraphQL errors', ['errors' => $data['errors']]);
                return null;
            }

            return $data['data'] ?? null;
        } catch (\Exception $e) {
            Log::error('GraphQL query failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return null;
        }
    }

    public function fetchBookByAuthorAndTitle(string $authorName, string $bookTitle): ?array
    {
        $result = $this->executeGraphQL(
            $this->getBookByAuthorAndTitleQuery(),
            [
                'authorName' => $authorName,
                'bookTitle' => $bookTitle,
            ]
        );

        if (!$result || empty($result['contributions'])) {
            Log::info('No contributions found', [
                'author' => $authorName,
                'title' => $bookTitle,
            ]);
            return null;
        }

        return $this->parseContributionResult($result['contributions'][0]);
    }

    private function parseContributionResult(array $contribution): ?array
    {
        $book = $contribution['book'] ?? [];
        $author = $contribution['author'] ?? null;

        return [
            'contribution_id' => $contribution['id'] ?? null,
            'book_id' => $book['id'] ?? null,
            'title' => $book['title'] ?? null,
            'year' => $book['release_year'] ?? null,
            'rating' => $book['rating'] ?? null,
            'pages' => $book['pages'] ?? null,
            'author' => $author['name'] ?? null,
            'cover_url' => $book['image']['url'] ?? null,
            'cover_width' => $book['image']['width'] ?? null,
            'cover_height' => $book['image']['height'] ?? null,
            'created_at' => $contribution['created_at'] ?? null,
        ];
    }
}