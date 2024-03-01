<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::where('username', 'test')->orWhere('email', 'test@test.fr')->delete();

        User::factory()->create([
            'username' => 'test',
            'email' => 'test@test.fr',
            'password' => bcrypt('test'),
            'is_admin' => true,
        ])
            ->each(function ($user) {
                Location::factory()->create([
                    'user_id' => $user->id,
                ]);
            });
    }
}
