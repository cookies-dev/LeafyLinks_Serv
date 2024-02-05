<?php

namespace Plant;

use App\Models\Location;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantTest extends TestCase
{
    use RefreshDatabase;


    private User $user;
    private Location $location;
    private Collection $plants;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->location = Location::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->plants = Plant::factory()->count(3)->create([
            'location_id' => $this->location->id,
        ]);
    }

    public function testGetPlant()
    {
        /** @var Plant $plant */
        $plant = $this->plants->first();
        $response = $this->getJson(route('plant.get', ['id' => $plant->id]));
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $plant->toArray(),
        ]);
    }

    public function testSearchPlant()
    {
        /** @var Plant $plant */
        $plant = $this->plants->first();
        $response = $this->getJson(route('plant.search', ['query' => $plant->name, 'limit' => 1]));
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$plant->toArray()],
        ]);
    }

    public function testGetPlants()
    {
        $response = $this->getJson(route('location.plants', ['idLocation' => $this->location->id]));
        $response->assertStatus(200);
        $response->assertJson(['data' => $this->plants->toArray()]);
        $response->assertJsonCount($this->plants->count(), 'data');
    }

    public function testCreatePlantMissingFields()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->postJson(route('plant.create'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['location_id', 'trefle_id', 'name', 'desc']);
    }

    public function testCreatePlantDoesntExist()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->postJson(route('plant.create'), [
            'location_id' => 999,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(404);
    }

    public function testCreatePlantNotOwner()
    {
        $location = Location::factory()->create();

        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->postJson(route('plant.create'), [
            'location_id' => $location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized', 'message' => 'You are not the owner of this location']);
    }

    public function testCreatePlant()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->postJson(route('plant.create'), [
            'location_id' => $this->location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(201);
        $response->assertJson(['message' => 'Plant created successfully']);
        $response->assertJsonStructure(['data' => ['id', 'location_id', 'trefle_id', 'name', 'desc', 'created_at', 'updated_at']]);
        $this->assertDatabaseHas('plants', [
            'location_id' => $this->location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
    }


    public function testEditPlantMissingFields()
    {
        Sanctum::actingAs($this->user);
        /** @var Plant $plant */
        $plant = $this->plants->first();
        $response = $this->actingAs($this->user)->putJson(route('plant.edit', ['id' => $plant->id]), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['location_id', 'trefle_id', 'name', 'desc']);
    }

    public function testEditPlantDoesntExist()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->putJson(route('plant.edit', ['id' => 999]), [
            'location_id' => $this->location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(404);

    }

    public function testEditPlantNotOwner()
    {
        $location = Location::factory()->create();
        $plant = Plant::factory()->create([
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->putJson(route('plant.edit', ['id' => $plant->id]), [
            'location_id' => $location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized', 'message' => 'You are not the owner of current location']);
    }

    public function testEditPlantNotOwnerNewLocation()
    {
        $location = Location::factory()->create();
        $plant = Plant::factory()->create([
            'location_id' => $this->location->id,
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->putJson(route('plant.edit', ['id' => $plant->id]), [
            'location_id' => $location->id,
            'trefle_id' => 1,
            'name' => 'test',
            'desc' => 'test',
        ]);
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized', 'message' => 'You are not the owner of new location']);
    }

    public function testEditPlant()
    {
        Sanctum::actingAs($this->user);
        /** @var Plant $plant */
        $plant = $this->plants->first();
        $newPlant = Plant::factory()->make([
            'location_id' => $this->location->id,
        ]);
        $response = $this->actingAs($this->user)->putJson(route('plant.edit', ['id' => $plant->id]), $newPlant->toArray());
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Plant updated successfully']);
        $response->assertJsonStructure(['data' => ['id', 'location_id', 'trefle_id', 'name', 'desc', 'created_at', 'updated_at']]);
        $this->assertDatabaseHas('plants', $newPlant->toArray());
    }


    public function testDeletePlantDoesntExist()
    {
        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->deleteJson(route('plant.delete', ['id' => 999]));
        $response->assertStatus(404);
    }

    public function testDeletePlantNotOwner()
    {
        $location = Location::factory()->create();
        $plant = Plant::factory()->create([
            'location_id' => $location->id,
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->actingAs($this->user)->deleteJson(route('plant.delete', ['id' => $plant->id]));
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Unauthorized', 'message' => 'You are not the owner of this location']);
    }

    public function testDeletePlant()
    {
        Sanctum::actingAs($this->user);
        /** @var Plant $plant */
        $plant = $this->plants->first();
        $response = $this->actingAs($this->user)->deleteJson(route('plant.delete', ['id' => $plant->id]));
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Plant deleted successfully']);
        $this->assertDatabaseMissing('plants', $plant->toArray());
    }
}
