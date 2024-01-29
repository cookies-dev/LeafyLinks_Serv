<?php

namespace Location;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationGetTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        $this->locations = Location::factory()
            ->count(10)
            ->create()
            ->each(function ($location, $key) {
                $location->lat = $key * 0.01;
                $location->lng = $key * 0.01;
                $location->save();
            });
    }

    public function testGetNearestLocations()
    {
        $response = $this->get(Route('location.nearest', ['lat' => 0, 'lng' => 0]));
        $response->assertStatus(200);

        $data = $response->json()['data'];
        foreach ($data as $key => $location) {
            print_r($location['distance']);
            $this->assertEquals($key * 0.01, $location['lat']);
            $this->assertEquals($key * 0.01, $location['lng']);
        }
        $this->assertCount(10, $data);
    }

    public function testGetNearestLocationsWithMaxDistance()
    {
        $response = $this->get(Route('location.nearest', ['lat' => 0, 'lng' => 0, 'dist' => 7]));
        $response->assertStatus(200);

        $data = $response->json()['data'];
        foreach ($data as $key => $location) {
            $this->assertEquals($key * 0.01, $location['lat']);
            $this->assertEquals($key * 0.01, $location['lng']);
        }
        $this->assertCount(5, $data);
    }

    public function testGetLocationById()
    {
        $response = $this->get(Route('location.getId', ['id' => 1]));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(0, $data['lat']);
        $this->assertEquals(0, $data['lng']);
    }

    public function testGetUserLocations()
    {
        $response = $this->get(Route('location.user', ['userId' => 1]));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data);

        $location = $this->locations->where('user_id', 1)->first();
        $this->assertEquals($location->id, $data[0]['id']);
    }
}
