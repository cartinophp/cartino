<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasCustomFields;
    use HasFactory;
    use HasOptimizedFilters;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'email_verified_at',
        'password',
        'is_enabled',
        'last_login_at',
        'last_login_ip',
        'avatar',
        'meta',
        'data',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'is_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'meta' => 'array',
        'password' => 'hashed',
    ];

    protected $appends = [
        'full_name',
        'fidelity_card_number',
        'fidelity_points',
        'fidelity_card_status',
        'fidelity_tier',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [
        'groups:id,name',
    ];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'is_enabled',
        'last_login_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'is_enabled',
        'last_login_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_customer_group');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function fidelityCard(): HasOne
    {
        return $this->hasOne(FidelityCard::class);
    }

    public function fidelityTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(FidelityTransaction::class, FidelityCard::class);
    }

    public function getDefaultWishlistAttribute(): ?Wishlist
    {
        return $this->wishlists()->where('is_default', true)->first();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFidelityCardNumberAttribute(): ?string
    {
        return $this->fidelityCard?->card_number;
    }

    public function getFidelityPointsAttribute(): int
    {
        return $this->fidelityCard?->available_points ?? 0;
    }

    public function getFidelityCardStatusAttribute(): ?string
    {
        if (! $this->fidelityCard) {
            return null;
        }

        return $this->fidelityCard->is_active ? 'active' : 'inactive';
    }

    public function getFidelityTierAttribute(): ?array
    {
        if (! $this->fidelityCard) {
            return null;
        }

        return $this->fidelityCard->getCurrentTier();
    }

    // Fidelity Card Methods
    public function getOrCreateFidelityCard(): FidelityCard
    {
        return
            $this->fidelityCard ?: $this->fidelityCard()->create([
                'is_active' => true,
            ]);
    }

    public function getFidelityCardNumber(): ?string
    {
        return $this->fidelity_card_number;
    }

    public function getFidelityPoints(): int
    {
        return $this->fidelity_points;
    }

    public function addFidelityPoints(
        int $points,
        ?string $reason = null,
        ?int $orderId = null,
    ): ?FidelityTransaction {
        if (! config('cartino.fidelity.enabled')) {
            return null;
        }

        $card = $this->getOrCreateFidelityCard();

        return $card->addPoints($points, $reason, $orderId);
    }

    public function redeemFidelityPoints(
        int $points,
        ?string $reason = null,
        ?int $orderId = null,
    ): ?FidelityTransaction {
        if (! config('cartino.fidelity.enabled') || ! $this->fidelityCard) {
            return null;
        }

        return $this->fidelityCard->redeemPoints($points, $reason, $orderId);
    }

    public function canRedeemPoints(int $points): bool
    {
        return $this->fidelityCard?->canRedeemPoints($points) ?? false;
    }

    public function processOrderForFidelity(Order $order): ?FidelityTransaction
    {
        if (! config('cartino.fidelity.points.enabled')) {
            return null;
        }

        $card = $this->getOrCreateFidelityCard();
        $points = $card->calculatePointsForAmount($order->total, $order->currency);

        if ($points > 0) {
            // Aggiorna l'importo totale speso
            $card->increment('total_spent_amount', $order->total);

            return $card->addPoints($points, "Points earned from order #{$order->number}", $order->id);
        }

        return null;
    }
}
