<?php

use App\Models\Journal;
use App\Models\Moodboard;
use App\Models\User;

it('persists content ids on moodboard updates', function () {
    $user = User::factory()->create();
    $journal = Journal::factory()->create(['user_id' => $user->id]);

    $moodboard = Moodboard::factory()->create([
        'user_id' => $user->id,
        'journal_id' => $journal->id,
    ]);

    $moodboard->update([
        'color_ids' => [1, 2, 3],
        'artistic_period_id' => 10,
        'poem_id' => 20,
    ]);

    $moodboard->refresh();

    expect($moodboard->color_ids)->toBe([1, 2, 3]);
    expect($moodboard->artistic_period_id)->toBe(10);
    expect($moodboard->poem_id)->toBe(20);
});
