<?php

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_id',
        'order_id',
        'order_line_id',
        'rating',
        'title',
        'content',
        'is_verified_purchase',
        'is_approved',
        'is_featured',
        'helpful_count',
        'unhelpful_count',
        'replied_at',
        'reply_content',
        'replied_by',
    ];

    protected $casts = [
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'helpful_count' => 'integer',
        'unhelpful_count' => 'integer',
        'rating' => 'integer',
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $with = [];

    /**
     * Get the product that owns the review
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer that wrote the review
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order associated with the review
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order line associated with the review
     */
    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }

    /**
     * Get the admin user who replied to the review
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Get the media files for the review
     */
    public function reviewMedia(): HasMany
    {
        return $this->hasMany(ReviewMedia::class, 'review_id');
    }

    /**
     * Get the votes for the review
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class, 'review_id');
    }

    /**
     * Get helpful votes for the review
     */
    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(ReviewVote::class, 'review_id')->where('is_helpful', true);
    }

    /**
     * Get unhelpful votes for the review
     */
    public function unhelpfulVotes(): HasMany
    {
        return $this->hasMany(ReviewVote::class, 'review_id')->where('is_helpful', false);
    }

    /**
     * Scope to get approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope to get featured reviews
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get verified purchase reviews
     */
    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope to filter by rating
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to filter by minimum rating
     */
    public function scopeMinRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope to filter by maximum rating
     */
    public function scopeMaxRating($query, $rating)
    {
        return $query->where('rating', '<=', $rating);
    }

    /**
     * Get the review's helpfulness percentage
     */
    public function getHelpfulnessPercentageAttribute(): float
    {
        $totalVotes = $this->helpful_count + $this->unhelpful_count;

        if ($totalVotes === 0) {
            return 0;
        }

        return round(($this->helpful_count / $totalVotes) * 100, 2);
    }

    /**
     * Check if the review has a reply
     */
    public function getHasReplyAttribute(): bool
    {
        return ! empty($this->reply_content);
    }

    /**
     * Get the review's display name for the customer
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer?->name ?? 'Guest Customer';
    }

    /**
     * Get formatted rating display
     */
    public function getRatingDisplayAttribute(): string
    {
        return $this->rating.'/5 stars';
    }

    /**
     * Get truncated content for display
     */
    public function getShortContentAttribute(): string
    {
        return Str::limit($this->content, 150);
    }

    /**
     * Check if review was recently created (within last 7 days)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->isAfter(now()->subDays(7));
    }

    /**
     * Get the verification badge text
     */
    public function getVerificationBadgeAttribute(): string
    {
        return $this->is_verified_purchase ? 'Verified Purchase' : 'Unverified';
    }

    /**
     * Get the status badge text
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_featured) {
            return 'Featured';
        }

        return $this->is_approved ? 'Approved' : 'Pending';
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        if ($this->is_featured) {
            return 'purple';
        }

        return $this->is_approved ? 'green' : 'yellow';
    }
}
