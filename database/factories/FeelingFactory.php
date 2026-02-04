<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feeling>
 */
class FeelingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'joyful', 'melancholic', 'anxious', 'peaceful', 'excited',
                'nostalgic', 'contemplative', 'energetic', 'serene', 'restless'
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
