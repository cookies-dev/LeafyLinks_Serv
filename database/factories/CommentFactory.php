<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'plant_id' => \App\Models\Plant::factory(),
            'comment' => $this->faker->realText(200),
            'up_votes' => $this->faker->numberBetween(0, 10),
            'down_votes' => $this->faker->numberBetween(0, 10),
        ];
    }
}
