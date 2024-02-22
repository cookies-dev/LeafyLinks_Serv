<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->has(Plant::factory()->count(5))
            ->create();
    }
}
