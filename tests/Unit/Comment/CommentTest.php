<?php

namespace Comment;

use App\Models\Comment;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;


    private Plant $plant;
    private Collection $comments;


    protected function setUp(): void
    {
        parent::setUp();
        $this->plant = Plant::factory()->create();
        $this->comments = Comment::factory()->count(5)->create([
            'plant_id' => $this->plant->id,
        ]);
    }

    public function testGetComment()
    {
        $response = $this->getJson(route('plant.comments', ['plantId' => $this->plant->id]));
        $response->assertStatus(200);
        $response->assertJson(['data' => $this->comments->toArray()]);
        $response->assertJsonCount($this->comments->count(), 'data');
    }

    public function testGetCommentByIdDoesntExist()
    {
        $response = $this->getJson(route('comment.get', ['id' => 999]));
        $response->assertStatus(404);
    }

    public function testGetCommentById()
    {
        /** @var Comment $comment */
        $comment = $this->comments->first();
        $response = $this->getJson(route('comment.get', ['id' => $comment->id]));
        $response->assertStatus(200);
        $response->assertJson(['data' => $comment->toArray()]);
    }

    public function testCreateCommentMissingFields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson(route('comment.create'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['comment', 'plant_id']);
    }

    public function testCreateCommentDoesntExist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson(route('comment.create'), [
            'comment' => 'test',
            'plant_id' => 999,
        ]);
        $response->assertStatus(422);

    }

    public function testCreateComment()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson(route('comment.create'), [
            'comment' => 'test',
            'plant_id' => $this->plant->id,
        ]);
        $response->assertStatus(201);
        $response->assertJson(['message' => 'Comment created successfully']);
        $response->assertJsonStructure(['data' => ['id', 'comment', 'plant_id', 'created_at', 'updated_at']]);
        $this->assertDatabaseHas('comments', [
            'comment' => 'test',
            'plant_id' => $this->plant->id,
        ]);
    }


    public function testEditCommentDoesntExist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->putJson(route('comment.edit', ['id' => 999]), [
            'comment' => 'test',
        ]);
        $response->assertStatus(404);
    }

    public function testEditCommentNotOwner()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Comment $comment */
        $comment = $this->comments->first();
        $response = $this->putJson(route('comment.edit', ['id' => $comment->id]), [
            'comment' => 'test',
        ]);
        $response->assertStatus(403);
        $response->assertJson(['errors' => 'Unauthorized', 'message' => 'You are not the owner of this comment']);
    }

    public function testEditComment()
    {
        /** @var Comment $comment */
        $comment = $this->comments->first();
        Sanctum::actingAs($comment->user);
        $response = $this->putJson(route('comment.edit', ['id' => $comment->id]), [
            'comment' => 'test',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Comment updated']);
        $response->assertJsonStructure(['data' => ['id', 'comment', 'plant_id', 'created_at', 'updated_at']]);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment' => 'test',
        ]);
    }

    public function testDeleteCommentDoesntExist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->deleteJson(route('comment.delete', ['id' => 999]));
        $response->assertStatus(404);
    }

    public function testDeleteCommentNotOwner()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Comment $comment */
        $comment = $this->comments->first();
        $response = $this->deleteJson(route('comment.delete', ['id' => $comment->id]));
        $response->assertStatus(403);
        $response->assertJson(['errors' => 'Unauthorized', 'message' => 'You are not the owner of this comment']);
    }

    public function testDeleteComment()
    {
        /** @var Comment $comment */
        $comment = $this->comments->first();
        Sanctum::actingAs($comment->user);
        $response = $this->deleteJson(route('comment.delete', ['id' => $comment->id]));
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Comment deleted']);
        $this->assertDatabaseMissing('comments', $comment->toArray());
    }

    public function testDeleteCommentOwnerLocation()
    {
        Sanctum::actingAs($this->plant->location->user);
        $comment = $this->comments->first();
        $response = $this->deleteJson(route('comment.delete', ['id' => $comment->id]));
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Comment deleted']);
        $this->assertDatabaseMissing('comments', $comment->toArray());
    }
}
