<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_id',
        'user_id',
        'path',
        'is_cover',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_cover' => 'boolean',
    ];

    /**
     * Get the recipe that owns the photo.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the user that uploaded the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include cover photos.
     */
    public function scopeCover($query)
    {
        return $query->where('is_cover', true);
    }

    /**
     * Scope a query to only include non-cover photos.
     */
    public function scopeNotCover($query)
    {
        return $query->where('is_cover', false);
    }

    /**
     * Get the full URL for the photo.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Get the thumbnail URL for the photo.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $pathInfo = pathinfo($this->path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['basename'];
        return asset('storage/' . $thumbnailPath);
    }
}
