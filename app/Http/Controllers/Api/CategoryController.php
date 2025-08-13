<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::withCount(['recipes', 'publicRecipes'])
                             ->orderBy('name')
                             ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:60|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => [
                'category' => $category
            ]
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['recipes' => function ($query) {
            $query->public()->with(['user', 'coverPhoto', 'ratings']);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category
            ]
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:60|unique:categories,name,' . $category->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => [
                'category' => $category
            ]
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has recipes
        if ($category->recipes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with recipes'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
