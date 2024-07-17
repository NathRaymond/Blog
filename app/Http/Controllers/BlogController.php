<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::all();
        return response()->json($blogs, 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $blog = Blog::create($request->all());

            return response()->json(['message' => 'Blog created successfully.', 'data' => $blog], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation Error.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create blog.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $blog = Blog::with('posts')->findOrFail($id);
            return response()->json($blog, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
            ]);

            $blog = Blog::findOrFail($id);
            $blog->update($request->all());

            return response()->json(['message' => 'Blog updated successfully.', 'data' => $blog], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Blog not found.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation Error.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update blog.', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Blog::destroy($id);
            return response()->json(['message' => 'Blog deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete blog.', 'message' => $e->getMessage()], 500);
        }
    }
}