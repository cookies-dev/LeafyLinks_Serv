<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="Endpoints for managing comments"
 * )
 */
class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/plant/{plantId}/comment/",
     *     summary="Get comments by plant ID",
     *     tags={"Plants"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Plant ID"
     *     ),
     *     @OA\Response(response="200", description="List of comments for the plant"),
     *     @OA\Response(response="404", description="No comments found for the plant")
     * )
     */
    public function get(int $plantId)
    {
        $comments = Comment::where('plant_id', $plantId)->get();
        return response()->json(['data' => $comments->toArray()], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/comments/{id}",
     *     summary="Get comment by ID",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Comment ID"
     *     ),
     *     @OA\Response(response="200", description="Comment details"),
     *     @OA\Response(response="404", description="Comment not found")
     * )
     */
    public function getById($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json(['data' => $comment], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Comment data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="plant_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Comment created successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Plant not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'comment' => 'required|string',
            'plant_id' => 'required|integer|exists:plants,id'
        ]);

        $comment = new Comment($request->all());
        $comment->user_id = Auth::id();
        $comment->save();
        return response()->json(['message' => 'Comment created successfully', 'data' => $comment], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="Edit a comment",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Updated comment data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="comment", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Comment ID"
     *     ),
     *     @OA\Response(response="200", description="Comment updated successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Comment not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of this comment'],
                403
            );
        }

        $comment->comment = $request->comment;
        $comment->save();

        return response()->json(['message' => 'Comment updated', 'data' => $comment], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Comment ID"
     *     ),
     *     @OA\Response(response="200", description="Comment deleted successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Comment not found")
     * )
     */
    public function delete($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== Auth::id() && $comment->plant->location->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of this comment'],
                403
            );
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted'], 200);
    }
}
