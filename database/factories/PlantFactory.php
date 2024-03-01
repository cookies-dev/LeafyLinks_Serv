<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plant>
 */
class PlantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => \App\Models\Location::factory(),
            'trefle_id' => $this->faker->numberBetween(1, 20000),
            'name' => $this->faker->text(20),
            'desc' => $this->faker->realText(200),
        ];
    }
}
