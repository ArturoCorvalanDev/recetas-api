<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    /**
     * Display a listing of ingredients.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ingredient::withCount(['recipes', 'publicRecipes']);

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        $ingredients = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $ingredients
        ]);
    }

    /**
     * Store a newly created ingredient.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120|unique:ingredients',
            'default_unit' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $ingredient = Ingredient::create([
            'name' => $request->name,
            'default_unit' => $request->default_unit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ingredient created successfully',
            'data' => [
                'ingredient' => $ingredient
            ]
        ], 201);
    }

    /**
     * Display the specified ingredient.
     */
    public function show(Ingredient $ingredient): JsonResponse
    {
        $ingredient->load(['recipes' => function ($query) {
            $query->public()->with(['user', 'coverPhoto', 'ratings']);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'ingredient' => $ingredient
            ]
        ]);
    }

    /**
     * Update the specified ingredient.
     */
    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120|unique:ingredients,name,' . $ingredient->id,
            'default_unit' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $ingredient->update([
            'name' => $request->name,
            'default_unit' => $request->default_unit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ingredient updated successfully',
            'data' => [
                'ingredient' => $ingredient
            ]
        ]);
    }

    /**
     * Remove the specified ingredient.
     */
    public function destroy(Ingredient $ingredient): JsonResponse
    {
        // Check if ingredient is used in recipes
        if ($ingredient->recipes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete ingredient that is used in recipes'
            ], 400);
        }

        $ingredient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ingredient deleted successfully'
        ]);
    }

    /**
     * Search ingredients by name.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $ingredients = Ingredient::search($request->q)
                                ->withCount(['recipes', 'publicRecipes'])
                                ->orderBy('name')
                                ->limit(10)
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $ingredients
        ]);
    }
}
