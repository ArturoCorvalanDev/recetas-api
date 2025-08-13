<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'default_unit',
    ];

    /**
     * Get the recipes that use this ingredient.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
                    ->withPivot(['quantity', 'unit', 'note'])
                    ->withTimestamps();
    }

    /**
     * Get the public recipes that use this ingredient.
     */
    public function publicRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
                    ->where('is_public', true)
                    ->withPivot(['quantity', 'unit', 'note'])
                    ->withTimestamps();
    }

    /**
     * Get the recipes count for the ingredient.
     */
    public function getRecipesCountAttribute(): int
    {
        return $this->recipes()->count();
    }

    /**
     * Get the public recipes count for the ingredient.
     */
    public function getPublicRecipesCountAttribute(): int
    {
        return $this->publicRecipes()->count();
    }

    /**
     * Scope a query to search ingredients by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
