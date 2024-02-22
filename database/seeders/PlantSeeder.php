<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->create()
            ->each(function ($user) {
                $location = Location::factory()->create([
                    'user_id' => $user->id,
                ]);
                Plant::factory()->count(5)->create([
                    'location_id' => $location->id,
                ]);
            });
    }
}
