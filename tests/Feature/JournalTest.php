<?php

use App\Models\Journal;
use App\Models\User;

it('creates a journal in the singular table', function () {
    $user = User::factory()->create();

    $journal = Journal::create([
        'user_id' => $user->id,
        'creation_date' => now()->toDateString(),
    ]);

    expect($journal->uuid)->toBeString()->not->toBeEmpty();

    $this->assertDatabaseHas('journal', [
        'id' => $journal->id,
        'user_id' => $user->id,
    ]);
});
