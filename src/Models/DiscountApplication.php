<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscountApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'discount_id',
        'applicable_type',
        'applicable_id',
        'discount_amount',
        'applied_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    // Relationships
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function applicable(): MorphTo
    {
        return $this->morphTo();
    }
}
