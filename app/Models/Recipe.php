<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'prep_minutes',
        'cook_minutes',
        'servings',
        'difficulty',
        'is_public',
        'calories',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'prep_minutes' => 'integer',
        'cook_minutes' => 'integer',
        'servings' => 'integer',
        'calories' => 'integer',
    ];

    protected $appends = [
        'total_time',
        'average_rating',
        'ratings_count',
        'favorites_count',
        'comments_count',
        'difficulty_text',
        'url',
        'is_favorite',
    ];

    /**
     * The difficulty levels available.
     */
    public const DIFFICULTY_LEVELS = [
        'easy' => 'Fácil',
        'medium' => 'Medio',
        'hard' => 'Difícil',
    ];

    protected $isFavorite = false;


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            if (empty($recipe->slug)) {
                $recipe->slug = Str::slug($recipe->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }



    public function setIsFavorite(bool $value)
    {
        $this->isFavorite = $value;
    }
    /**
     * Get the user that owns the recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the steps for the recipe.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    /**
     * Get the ingredients for the recipe.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot(['quantity', 'unit', 'note'])
            ->withTimestamps();
    }

    /**
     * Get the categories for the recipe.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recipe_categories');
    }

    /**
     * Get the photos for the recipe.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the cover photo for the recipe.
     */
    public function coverPhoto(): HasMany
    {
        return $this->hasMany(Photo::class)->where('is_cover', true);
    }

    /**
     * Get the comments for the recipe.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');;
    }

    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Get the ratings for the recipe.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the users who favorited this recipe.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }


    /**
     * Scope a query to only include public recipes.
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true);
    }

    /**
     * Scope a query to filter by difficulty.
     */
    public function scopeByDifficulty(Builder $query, string $difficulty): void
    {
        $query->where('difficulty', $difficulty);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, $categoryId): void
    {
        $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    /**
     * Scope a query to search recipes.
     */
    public function scopeSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the total cooking time in minutes.
     */
    public function getTotalTimeAttribute(): int
    {
        return ($this->prep_minutes ?? 0) + ($this->cook_minutes ?? 0);
    }

    /**
     * Get the average rating for the recipe.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Get the total number of ratings for the recipe.
     */
    public function getRatingsCountAttribute(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Get the total number of favorites for the recipe.
     */
    public function getFavoritesCountAttribute(): int
    {
        return $this->favoritedBy()->count();
    }

    /**
     * Get the difficulty level in Spanish.
     */
    public function getDifficultyTextAttribute(): string
    {
        return self::DIFFICULTY_LEVELS[$this->difficulty] ?? 'Desconocido';
    }

    /**
     * Get the URL for the recipe.
     */
    public function getUrlAttribute(): string
    {
        return route('recipes.show', $this->slug);
    }

    public function getIsFavoriteAttribute()
    {
        $user = auth()->user(); // usuario logueado

        if (!$user) {
            return false; // o null si prefieres
        }

        // Verifica si el usuario ha marcado la receta como favorita
        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
