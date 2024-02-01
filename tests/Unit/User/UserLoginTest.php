<?php


namespace User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginUserWithMissingEmail()
    {
        $response = $this->post(Route('user.login'), [
            'password' => 'test1',
        ]);
        $response->assertStatus(400);
    }

    public function testLoginUserWithMissingPassword()
    {
        $response = $this->post(Route('user.login'), [
            'email' => 'test@test.com'
        ]);
        $response->assertStatus(400);
    }

    public function testLoginUser()
    {
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => 'test1'
        ]);

        $response = $this->post(Route('user.login'), [
            'email' => 'test@test.com',
            'password' => 'test1'
        ]);
        $response->assertStatus(200);
    }

    public function testLogout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->post(Route('user.logout'))->assertStatus(200);
    }
}
