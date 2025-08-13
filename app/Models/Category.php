<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the recipes for the category.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_categories');
    }

    /**
     * Get the public recipes for the category.
     */
    public function publicRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_categories')
                    ->where('is_public', true);
    }

    /**
     * Get the recipes count for the category.
     */
    public function getRecipesCountAttribute(): int
    {
        return $this->recipes()->count();
    }

    /**
     * Get the public recipes count for the category.
     */
    public function getPublicRecipesCountAttribute(): int
    {
        return $this->publicRecipes()->count();
    }
}
