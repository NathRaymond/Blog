<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $blog_id, $post_id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $post = Post::findOrFail($post_id);

            $comment = new Comment();
            $comment->comment = $request->input('comment');
            $comment->post_id = $post->id;
            $comment->save();

            return response()->json(['message' => 'Comment created successfully.', 'data' => $comment], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store comment.', 'message' => $e->getMessage()], 400);
        }
    }

}