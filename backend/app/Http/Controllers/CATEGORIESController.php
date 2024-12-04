<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CATEGORIESController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        if ($categories->count() > 0) {
            return response()->json([
                'status' => 200,
                'categories' => $categories
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $category = Category::create([
            'category_name' => $request->category_name,
            'description' => $request->description
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Category created successfully.',
            'category' => $category
        ], 200);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json([
                'status' => 200,
                'category' => $category
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $category = Category::find($id);
        if ($category) {
            $category->update([
                'category_name' => $request->category_name,
                'description' => $request->description
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Category updated successfully.',
                'category' => $category
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Category deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found.'
            ], 404);
        }
    }
}