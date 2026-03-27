<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'customer_id',
        'is_helpful',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the review that owns the vote
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'review_id');
    }

    /**
     * Get the customer who made the vote
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope to get helpful votes
     */
    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    /**
     * Scope to get unhelpful votes
     */
    public function scopeUnhelpful($query)
    {
        return $query->where('is_helpful', false);
    }

    /**
     * Get the vote type display
     */
    public function getVoteTypeAttribute(): string
    {
        return $this->is_helpful ? 'Helpful' : 'Not Helpful';
    }

    /**
     * Get the vote icon
     */
    public function getIconAttribute(): string
    {
        return $this->is_helpful ? 'thumb-up' : 'thumb-down';
    }

    /**
     * Get the vote color
     */
    public function getColorAttribute(): string
    {
        return $this->is_helpful ? 'green' : 'red';
    }
}
