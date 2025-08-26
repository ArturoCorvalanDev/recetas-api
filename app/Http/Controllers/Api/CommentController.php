<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a recipe.
     */
    public function index(Recipe $recipe): JsonResponse
    {
        $comments = $recipe->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Recipe $recipe): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar si la receta es pública o si el usuario la posee
            if (!$recipe->is_public && $recipe->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipe not found'
                ], 404);
            }

            // Crear el comentario
            $comment = $recipe->comments()->create([
                'user_id' => $request->user()->id,
                'content' => $request->content,
            ]);

            // Cargar relación de usuario
            $comment->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => [
                    'comment' => $comment
                ]
            ], 201);
        } catch (\Throwable $th) {
            // Devolver error en caso de excepción
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Check if user owns the comment
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => [
                'comment' => $comment
            ]
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        // Check if user owns the comment or is the recipe owner
        if ($comment->user_id !== $request->user()->id && $comment->recipe->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
