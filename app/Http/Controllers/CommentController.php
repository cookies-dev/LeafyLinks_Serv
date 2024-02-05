<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function get(int $plantId)
    {
        $comments = Comment::where('plant_id', $plantId)->get();
        return response()->json(['data' => $comments->toArray()], 200);
    }

    public function getById($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json(['data' => $comment], 200);
    }

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
