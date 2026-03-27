<?php

namespace Cartino\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'user_id',
        'rating',
        'title',
        'review',
        'version_reviewed',
        'is_verified_purchase',
        'is_featured',
        'status',
        'approved_at',
        'approved_by',
        'helpful_count',
        'not_helpful_count',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_verified_purchase' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeVerifiedPurchase($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    // Accessors
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getStarRatingAttribute(): string
    {
        return str_repeat('★', $this->rating).str_repeat('☆', 5 - $this->rating);
    }

    public function getHelpfulnessScoreAttribute(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return 0;
        }

        return ($this->helpful_count / $total) * 100;
    }

    // Methods
    public function approve(?int $approvedBy = null): bool
    {
        $updated = $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy ?? auth()->id(),
        ]);

        if ($updated) {
            $this->app->updateRating();
        }

        return $updated;
    }

    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }

    public function feature(): bool
    {
        return $this->update(['is_featured' => true]);
    }

    public function unfeature(): bool
    {
        return $this->update(['is_featured' => false]);
    }

    public function markHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function markNotHelpful(): void
    {
        $this->increment('not_helpful_count');
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::created(function ($review) {
            $review->app->updateRating();
        });

        static::updated(function ($review) {
            if ($review->wasChanged('rating') || $review->wasChanged('status')) {
                $review->app->updateRating();
            }
        });

        static::deleted(function ($review) {
            $review->app->updateRating();
        });
    }
}
