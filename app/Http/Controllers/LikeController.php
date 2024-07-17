<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class LikeController extends Controller
{

    public function store(Request $request, $blog_id, $post_id)
    {
        try {
            $post = Post::findOrFail($post_id);

            $existingLike = Like::where('post_id', $post_id)->first();

            if ($existingLike) {
                return response()->json(['error' => 'Post already liked.'], 400);
            }

            $like = new Like();
            $like->post_id = $post->id;
            $like->save();

            return response()->json(['message' => 'Post liked successfully.', 'data' => $like], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Post not found.'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to like post.', 'message' => $e->getMessage()], 400);
        }
    }
}