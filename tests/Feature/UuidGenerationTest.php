<?php

use App\Models\Journal;
use App\Models\Moodboard;

test('journal assigns uuid on create', function () {
    $journal = Journal::factory()->create();

    expect($journal->uuid)->toBeString()->not->toBeEmpty();
});

test('moodboard assigns uuid on create', function () {
    $journal = Journal::factory()->create();
    $moodboard = Moodboard::factory()->create([
        'journal_id' => $journal->id,
    ]);

    expect($moodboard->uuid)->toBeString()->not->toBeEmpty();
});
