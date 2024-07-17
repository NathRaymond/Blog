<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function index($blog_id)
    {
        try {
            $posts = Post::where('blog_id', $blog_id)->get();
            return response()->json($posts, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found.'], 404);
        }
    }

    public function store(Request $request, $blog_id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $post = new Post();
            $post->title = $request->input('title');
            $post->content = $request->input('content');
            $post->blog_id = $blog_id;
            $post->save();

            return response()->json(['message' => 'Post created successfully.', 'data' => $post], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation Error.', 'message' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create post.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($blog_id, $post_id)
    {
        try {
            $post = Post::where('blog_id', $blog_id)->findOrFail($post_id);
            return response()->json($post, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Post not found.'], 404);
        }
    }

    public function update(Request $request, $blog_id, $post_id)
    {
        try {
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
            ]);

            $post = Post::where('blog_id', $blog_id)->findOrFail($post_id);
            $post->update($request->all());

            return response()->json(['message' => 'Post updated successfully.', 'data' => $post], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Post not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation Error.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update post.', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($blog_id, $post_id)
    {
        try {
            $post = Post::where('blog_id', $blog_id)->findOrFail($post_id);
            $post->delete();

            return response()->json(['message' => 'Post deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Post not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete post.', 'message' => $e->getMessage()], 500);
        }
    }
}