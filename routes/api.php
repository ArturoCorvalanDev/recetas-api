<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\IngredientController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Public recipe routes
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{slug}', [RecipeController::class, 'show']);

    // Public category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Public ingredient routes
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::get('/ingredients/search', [IngredientController::class, 'search']);
    Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        // User profile routes
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Recipe management routes
        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
        Route::get('/my-recipes', [RecipeController::class, 'myRecipes']);
        Route::get('/favorites', [RecipeController::class, 'favorites']);
        Route::post('/recipes/{recipe}/favorite', [RecipeController::class, 'toggleFavorite']);

        // Comment routes
        Route::get('/recipes/{recipe}/comments', [CommentController::class, 'index']);
        Route::post('/recipes/{recipe}/comments', [CommentController::class, 'store']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

        // Rating routes
        Route::get('/recipes/{recipe}/ratings', [RatingController::class, 'index']);
        Route::post('/recipes/{recipe}/ratings', [RatingController::class, 'store']);
        Route::put('/ratings/{rating}', [RatingController::class, 'update']);
        Route::delete('/ratings/{rating}', [RatingController::class, 'destroy']);
        Route::get('/recipes/{recipe}/my-rating', [RatingController::class, 'userRating']);

        // Admin routes (you might want to add admin middleware)
        Route::prefix('admin')->group(function () {
            Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
            Route::apiResource('ingredients', IngredientController::class)->except(['index', 'show', 'search']);
        });
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});
