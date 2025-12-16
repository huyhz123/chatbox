<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'title',
        'comment',
        'is_verified',
        'is_approved',
        'helpful_count',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
        'rating' => 'integer',
        'helpful_count' => 'integer',
    ];

    /**
     * Get the user that wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reviewable model (Product, Service, File, Course)
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: Only approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope: Only verified purchase reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Filter by rating
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
