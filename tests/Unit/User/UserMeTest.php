<?php


namespace User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserMeTest extends TestCase
{
    use RefreshDatabase;

    /*
     * Test that a user can get their own information
     * Test that a user can update their own information
     */

    public function testGetUserInformation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post(Route('user.me'));
        $response->assertStatus(200);

        $data = $response->json()['data'];
        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->username, $data['username']);
        $this->assertEquals($user->email, $data['email']);
    }

    public function testUpdateUserInformationEmailAlreadyExists()
    {
        $user = User::factory()->create(['email' => 'test1@test.com']);
        Sanctum::actingAs($user);
        $user2 = User::factory()->create(['email' => 'test2@test.com']);

        $response = $this->put(Route('user.update'), [
            'email' => $user2->email
        ]);

        $response->assertStatus(400);
    }

    public function testUpdateUserInformationUsernameAlreadyExists()
    {
        $user = User::factory()->create(['username' => 'test1']);
        Sanctum::actingAs($user);
        $user2 = User::factory()->create(['username' => 'test2']);

        $response = $this->put(Route('user.update'), [
            'username' => $user2->username
        ]);

        $response->assertStatus(400);
    }

    public function testUpdateUserInformationCantEditIsBotanic()
    {
        $user = User::factory()->create(['is_botanic' => true]);
        Sanctum::actingAs($user);

        $response = $this->put(Route('user.update'), [
            'is_botanic' => false
        ]);

        $response->assertStatus(400);
    }

    public function testUpdateUserInformationCantEditIsGarden()
    {
        $user = User::factory()->create(['is_garden' => true]);
        Sanctum::actingAs($user);

        $response = $this->put(Route('user.update'), [
            'is_garden' => false
        ]);

        $response->assertStatus(400);
    }

    public function testUpdateUserInformation()
    {
        $user = User::factory()->create([
            'username' => 'test1',
            'email' => 'test1@test.com',
            'password' => 'password',
            'phone' => '1234567890',
            'first_name' => 'first',
            'last_name' => 'last',
            'profile_picture' => 'picture',
            'bio' => 'bio'
        ]);
        Sanctum::actingAs($user);

        $response = $this->actingAs($user)->put(Route('user.update'), [
            'username' => 'test2',
            'email' => 'test2@test.com',
            'password' => 'password2',
            'phone' => '0987654321',
            'first_name' => 'first2',
            'last_name' => 'last2',
            'profile_picture' => 'picture2',
            'bio' => 'bio2'
        ]);
        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('test2@test.com', $user->email);
        $this->assertEquals('test2', $user->username);
        $this->assertEquals('0987654321', $user->phone);
        $this->assertEquals('first2', $user->first_name);
        $this->assertEquals('last2', $user->last_name);
        $this->assertEquals('picture2', $user->profile_picture);
        $this->assertEquals('bio2', $user->bio);
    }
}
