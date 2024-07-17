<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        try {
            $blogs = Blog::all();
            return response()->json(['message' => 'Blogs fetched successfully.', 'data' => $blogs], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch blogs.', 'message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            $blog = Blog::create($request->all());
            return response()->json(['message' => 'Blog created successfully.', 'data' => $blog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create blog.', 'message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        try {
            $blog = Blog::with('posts')->findOrFail($id);
            return response()->json(['message' => 'Blog fetched successfully.', 'data' => $blog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch blog.', 'message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        try {
            $blog = Blog::findOrFail($id);
            $blog->update($request->all());
            return response()->json(['message' => 'Blog updated successfully.', 'data' => $blog], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update blog.', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        try {
            Blog::destroy($id);
            return response()->json(['message' => 'Blog deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete blog.', 'message' => $e->getMessage()], 400);
        }
    }
}