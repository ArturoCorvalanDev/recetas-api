<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Recipe::with(['user', 'categories', 'coverPhoto', 'ratings'])
                      ->public();

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && in_array($request->difficulty, ['easy', 'medium', 'hard'])) {
            $query->byDifficulty($request->difficulty);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by max time
        if ($request->has('max_time')) {
            $query->whereRaw('(prep_minutes + cook_minutes) <= ?', [$request->max_time]);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'title', 'average_rating', 'favorites_count'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $recipes = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $recipes
        ]);
    }

    /**
     * Store a newly created recipe.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'prep_minutes' => 'nullable|integer|min:0',
            'cook_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'is_public' => 'boolean',
            'calories' => 'nullable|integer|min:0',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'nullable|numeric|min:0',
            'ingredients.*.unit' => 'nullable|string|max:20',
            'ingredients.*.note' => 'nullable|string|max:255',
            'steps' => 'array',
            'steps.*.step_number' => 'required|integer|min:1',
            'steps.*.instruction' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $recipe = Recipe::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'prep_minutes' => $request->prep_minutes,
                'cook_minutes' => $request->cook_minutes,
                'servings' => $request->servings,
                'difficulty' => $request->difficulty,
                'is_public' => $request->get('is_public', true),
                'calories' => $request->calories,
            ]);

            // Attach categories
            if ($request->has('categories')) {
                $recipe->categories()->attach($request->categories);
            }

            // Attach ingredients
            if ($request->has('ingredients')) {
                foreach ($request->ingredients as $ingredient) {
                    $recipe->ingredients()->attach($ingredient['ingredient_id'], [
                        'quantity' => $ingredient['quantity'] ?? null,
                        'unit' => $ingredient['unit'] ?? null,
                        'note' => $ingredient['note'] ?? null,
                    ]);
                }
            }

            // Create steps
            if ($request->has('steps')) {
                foreach ($request->steps as $step) {
                    $recipe->steps()->create([
                        'step_number' => $step['step_number'],
                        'instruction' => $step['instruction'],
                    ]);
                }
            }

            DB::commit();

            $recipe->load(['user', 'categories', 'ingredients', 'steps']);

            return response()->json([
                'success' => true,
                'message' => 'Recipe created successfully',
                'data' => [
                    'recipe' => $recipe
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified recipe.
     */
    public function show(string $slug): JsonResponse
    {
        $recipe = Recipe::with([
            'user',
            'categories',
            'ingredients',
            'steps',
            'photos',
            'comments.user',
            'ratings.user'
        ])->where('slug', $slug)->first();

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        // Check if recipe is public or user owns it
        if (!$recipe->is_public && auth()->id() !== $recipe->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        // Increment view count (you might want to add a views column)
        // $recipe->increment('views');

        return response()->json([
            'success' => true,
            'data' => [
                'recipe' => $recipe
            ]
        ]);
    }

    /**
     * Update the specified recipe.
     */
    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'prep_minutes' => 'nullable|integer|min:0',
            'cook_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'difficulty' => 'sometimes|required|in:easy,medium,hard',
            'is_public' => 'boolean',
            'calories' => 'nullable|integer|min:0',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'ingredients' => 'array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'nullable|numeric|min:0',
            'ingredients.*.unit' => 'nullable|string|max:20',
            'ingredients.*.note' => 'nullable|string|max:255',
            'steps' => 'array',
            'steps.*.step_number' => 'required|integer|min:1',
            'steps.*.instruction' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $recipe->update($request->only([
                'title', 'description', 'prep_minutes', 'cook_minutes',
                'servings', 'difficulty', 'is_public', 'calories'
            ]));

            // Update categories
            if ($request->has('categories')) {
                $recipe->categories()->sync($request->categories);
            }

            // Update ingredients
            if ($request->has('ingredients')) {
                $recipe->ingredients()->detach();
                foreach ($request->ingredients as $ingredient) {
                    $recipe->ingredients()->attach($ingredient['ingredient_id'], [
                        'quantity' => $ingredient['quantity'] ?? null,
                        'unit' => $ingredient['unit'] ?? null,
                        'note' => $ingredient['note'] ?? null,
                    ]);
                }
            }

            // Update steps
            if ($request->has('steps')) {
                $recipe->steps()->delete();
                foreach ($request->steps as $step) {
                    $recipe->steps()->create([
                        'step_number' => $step['step_number'],
                        'instruction' => $step['instruction'],
                    ]);
                }
            }

            DB::commit();

            $recipe->load(['user', 'categories', 'ingredients', 'steps']);

            return response()->json([
                'success' => true,
                'message' => 'Recipe updated successfully',
                'data' => [
                    'recipe' => $recipe
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified recipe.
     */
    public function destroy(Request $request, Recipe $recipe): JsonResponse
    {
        // Check if user owns the recipe
        if ($recipe->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $recipe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's recipes.
     */
    public function myRecipes(Request $request): JsonResponse
    {
        $recipes = $request->user()
                          ->recipes()
                          ->with(['categories', 'coverPhoto', 'ratings'])
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $recipes
        ]);
    }

    /**
     * Get favorite recipes.
     */
    public function favorites(Request $request): JsonResponse
    {
        $recipes = $request->user()
                          ->favorites()
                          ->with(['user', 'categories', 'coverPhoto', 'ratings'])
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $recipes
        ]);
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(Request $request, Recipe $recipe): JsonResponse
    {
        $user = $request->user();
        
        if ($user->hasFavorited($recipe)) {
            $user->favorites()->detach($recipe->id);
            $message = 'Recipe removed from favorites';
        } else {
            $user->favorites()->attach($recipe->id);
            $message = 'Recipe added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_favorited' => $user->hasFavorited($recipe)
            ]
        ]);
    }
}
