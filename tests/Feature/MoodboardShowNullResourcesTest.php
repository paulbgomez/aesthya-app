<?php

use App\Models\Journal;
use App\Models\Moodboard;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders moodboard show when artistic period and poem are missing', function () {
    $user = User::factory()->create();
    $journal = Journal::factory()->create(['user_id' => $user->id]);

    $moodboard = Moodboard::factory()->create([
        'user_id' => $user->id,
        'journal_id' => $journal->id,
        'artistic_period_id' => null,
        'poem_id' => null,
        'color_ids' => [],
        'artwork_ids' => [],
        'music_ids' => [],
        'book_ids' => [],
    ]);

    $response = $this->actingAs($user)->get(route('moodboard.show', $moodboard->id));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Moodboards/Show')
        ->where('artisticPeriod', null)
        ->where('poem', null)
    );
});
