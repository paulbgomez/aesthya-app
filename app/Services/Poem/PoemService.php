<?php

namespace App\Services\Poem;

use App\Models\Poem;

class PoemService
{
    public function __construct(
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
            'content' => $poem['content'] ?? null,
        ];

        $createPoem = Poem::create($poemData);

        return $createPoem->id;
    }
}
