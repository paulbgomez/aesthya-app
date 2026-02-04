<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Moodboard>
 */
class MoodboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'journal_id' => 1, // Default journal ID
            'feeling' => fake()->randomElement([
                'joyful', 'melancholic', 'anxious', 'peaceful', 'excited'
            ]),
            'generation_context' => null,
            'artwork_ids' => [],
            'music_ids' => [],
            'video_ids' => [],
            'book_ids' => [],
        ];
    }

    public function withGenerationContext(): static
    {
        return $this->state(fn (array $attributes) => [
            'generation_context' => [
                'paintings' => [
                    ['title' => fake()->words(3, true), 'artist' => fake()->name()],
                ],
                'music' => [
                    ['title' => fake()->words(2, true), 'artist' => fake()->name(), 'year' => fake()->year()],
                ],
                'book' => [
                    'title' => fake()->words(4, true),
                    'author' => fake()->name(),
                ],
            ],
        ]);
    }
}
