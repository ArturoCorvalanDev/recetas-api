<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Display a listing of ratings for a recipe.
     */
    public function index(Recipe $recipe): JsonResponse
    {
        $ratings = $recipe->ratings()
                         ->with('user')
                         ->latest()
                         ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    /**
     * Store a newly created rating.
     */
    public function store(Request $request, Recipe $recipe): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if recipe is public or user owns it
        if (!$recipe->is_public && $recipe->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        // Check if user already rated this recipe
        $existingRating = $recipe->ratings()->where('user_id', $request->user()->id)->first();
        
        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this recipe'
            ], 400);
        }

        $rating = $recipe->ratings()->create([
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
        ]);

        $rating->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Rating added successfully',
            'data' => [
                'rating' => $rating
            ]
        ], 201);
    }

    /**
     * Update the specified rating.
     */
    public function update(Request $request, Rating $rating): JsonResponse
    {
        // Check if user owns the rating
        if ($rating->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $rating->update([
            'rating' => $request->rating,
        ]);

        $rating->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Rating updated successfully',
            'data' => [
                'rating' => $rating
            ]
        ]);
    }

    /**
     * Remove the specified rating.
     */
    public function destroy(Request $request, Rating $rating): JsonResponse
    {
        // Check if user owns the rating
        if ($rating->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $rating->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating deleted successfully'
        ]);
    }

    /**
     * Get user's rating for a recipe.
     */
    public function userRating(Request $request, Recipe $recipe): JsonResponse
    {
        $rating = $recipe->ratings()->where('user_id', $request->user()->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'rating' => $rating
            ]
        ]);
    }
}
