<?php

namespace App\Services\Poem;

use App\Models\Poem;

class PoemService
{
    public function __construct(
        private PoetryDBService $service = new PoetryDBService
    ) {}

    public function processPoemData(array $poem): ?int
    {
        $existingPoem = Poem::where('name', $poem['name'])->where('author', $poem['author'])->first();
        if ($existingPoem) {
            return $existingPoem->id;
        }

        $poemData = [
            'name' => $poem['name'],
            'author' => $poem['author'],
            'content' => $poem['content'] ?? $this->service->fetchPoemByAuthorAndTitle($poem['author'], $poem['name']),
        ];

        $createPoem = Poem::create($poemData);

        return $createPoem->id;
    }
}
