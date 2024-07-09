<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUserWithMissingUsername()
    {
        $response = $this->post(Route('user.register'), [
            'email' => 'test@test.com',
            'password' => 'test1',
        ]);
        $response->assertStatus(422);
    }

    public function testCreateUserWithMissingEmail()
    {
        $response = $this->post(Route('user.register'), [
            'username' => 'test',
            'password' => 'test1',
        ]);
        $response->assertStatus(422);
    }

    public function testCreateUserWithMissingPassword()
    {
        $response = $this->post(Route('user.register'), [
            'username' => 'test',
            'email' => 'test1@test.com',
        ]);
        $response->assertStatus(422);
    }

    public function testCreateUserWithExistingEmail()
    {
        User::factory()->create([
            'email' => 'test2@test.com'
        ]);
        $response = $this->post(Route('user.register'), [
            'username' => 'test',
            'email' => 'test2@test.com',
            'password' => 'test1',
        ]);
        $response->assertStatus(422);
    }

    public function testCreateUserWithValidData()
    {
        $response = $this->post(Route('user.register'), [
            'username' => 'test',
            'email' => 'test1@test.com',
            'password' => 'test1',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'test1@test.com'
        ]);
    }

}
