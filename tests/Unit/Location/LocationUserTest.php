<?php

namespace Tests\Unit\Location;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocationUserTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->locations = Location::factory()
            ->count(3)
            ->create()
            ->each(function ($location, $key) {
                /* @var Location $location */
                $location->user_id = $this->user->id;
                $location->lat = $key * 0.01;
                $location->lng = $key * 0.01;
                $location->save();
            });
    }

    public function testGetUserLocationsByIdDoesNotExist()
    {
        Sanctum::actingAs($this->user);
        $response = $this->get(Route('location.user', ['userId' => 999]));

        $response->assertStatus(200);
        $data = $response->json()['data'];
        $this->assertCount(0, $data);
    }

    public function testGetUserLocationsById()
    {
        Sanctum::actingAs($this->user);
        $response = $this->get(Route('location.user', ['userId' => $this->user->id]));

        $response->assertStatus(200);
        $data = $response->json()['data'];
        $this->assertCount(3, $data);
        foreach ($data as $key => $location) {
            $this->assertEquals($key * 0.01, $location['lat']);
            $this->assertEquals($key * 0.01, $location['lng']);
        }
    }

    public function testGetUserLocationsUnauthorized()
    {
        $response = $this->get(Route('location.get'));
        $response->assertStatus(302);
        $response->assertRedirect(Route('user.login'));
    }

    public function testGetUserLocations()
    {
        Sanctum::actingAs($this->user);
        $response = $this->get(Route('location.get'));

        $response->assertStatus(200);
        $data = $response->json()['data'];
        $this->assertCount(3, $data);
        foreach ($data as $key => $location) {
            $this->assertEquals($key * 0.01, $location['lat']);
            $this->assertEquals($key * 0.01, $location['lng']);
        }
    }

    public function testCreateLocation()
    {
        Sanctum::actingAs($this->user);
        $response = $this->postJson(Route('location.create'), [
            'lat' => 1.23,
            'lng' => 4.56,
            'name' => 'Test Location',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(201);
        $data = $response->json()['data'];
        $this->assertEquals(1.23, $data['lat']);
        $this->assertEquals(4.56, $data['lng']);
        $this->assertEquals('Test Location', $data['name']);
        $this->assertEquals($this->user->id, $data['user_id']);

        $this->assertDatabaseHas('locations', [
            'lat' => 1.23,
            'lng' => 4.56,
            'name' => 'Test Location',
            'address' => 'Test Address',
            'user_id' => $this->user->id,
        ]);
    }

    public function testUpdateLocation()
    {
        Sanctum::actingAs($this->user);
        $response = $this->putJson(Route('location.edit', ['id' => $this->locations->first()->id]), [
            'lat' => 1.23,
            'lng' => 4.56,
            'name' => 'Test Location',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(200);
        $data = $response->json()['data'];
        $this->assertEquals(1.23, $data['lat']);
        $this->assertEquals(4.56, $data['lng']);
        $this->assertEquals('Test Location', $data['name']);
        $this->assertEquals($this->user->id, $data['user_id']);

        $this->assertDatabaseHas('locations', [
            'lat' => 1.23,
            'lng' => 4.56,
            'name' => 'Test Location',
            'address' => 'Test Address',
            'user_id' => $this->user->id,
        ]);
    }

    public function testDeleteLocation()
    {
        Sanctum::actingAs($this->user);
        $response = $this->deleteJson(Route('location.delete', ['id' => $this->locations->first()->id]));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('locations', [
            'id' => $this->locations->first()->id,
        ]);
    }
}
