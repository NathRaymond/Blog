<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function store(Request $request, $blog_id, $post_id)
    {
        try {
            $request->validate([
                'comment' => 'required|string',
            ]);

            $post = Post::findOrFail($post_id);

            $comment = new Comment();
            $comment->comment = $request->input('comment');
            $comment->post_id = $post->id;
            $comment->save();

            return response()->json(['message' => 'Comment added successfully.', 'data' => $comment], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation Error.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add comment.', 'message' => $e->getMessage()], 500);
        }
    }
}