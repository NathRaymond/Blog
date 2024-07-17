<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function store(Request $request, $blog_id, $post_id)
    {
        try {
            $post = Post::findOrFail($post_id);
            return response()->json(['message' => 'Post liked successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to like post.', 'message' => $e->getMessage()], 400);
        }
    }
}