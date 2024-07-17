<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Post;

class PostController extends Controller
{
    public function store(Request $request, $blog_id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            $blog = Blog::findOrFail($blog_id);

            $post = new Post();
            $post->title = $request->input('title');
            $post->content = $request->input('content');
            $post->blog_id = $blog->id;
            $post->save();

            return response()->json(['message' => 'Post created successfully.', 'data' => $post], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create post.', 'message' => $e->getMessage()], 400);
        }
    }

    public function show($blog_id, $post_id)
    {
        try {
            $post = Post::with(['likes', 'comments'])->findOrFail($post_id);

            return response()->json(['message' => 'Post fetched successfully.', 'data' => $post], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch post.', 'message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $blog_id, $post_id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        try {
            $post = Post::findOrFail($post_id);
            $post->update($request->all());

            return response()->json(['message' => 'Post updated successfully.', 'data' => $post], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update post.', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($blog_id, $post_id)
    {
        try {
            Post::destroy($post_id);

            return response()->json(['message' => 'Post deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete post.', 'message' => $e->getMessage()], 400);
        }
    }
}