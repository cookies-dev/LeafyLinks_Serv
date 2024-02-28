<?php

namespace Database\Seeders;

use App\Models\Comment;
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
                ])->each(function ($plant) {
                    Comment::factory()->count(3)->create([
                        'plant_id' => $plant->id,
                    ]);
                });
            });

        User::where('username', 'test')->orWhere('email', 'test@test.fr')->delete();

        User::factory()->create([
            'username' => 'test',
            'email' => 'test@test.fr',
            'password' => bcrypt('test'),
        ])
            ->each(function ($user) {
                Location::factory()->create([
                    'user_id' => $user->id,
                ]);
            });
    }
}
