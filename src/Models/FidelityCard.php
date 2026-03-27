<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FidelityCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'card_number',
        'total_points',
        'available_points',
        'total_earned',
        'total_redeemed',
        'total_spent_amount',
        'is_active',
        'issued_at',
        'last_activity_at',
        'meta',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'total_earned' => 'integer',
        'total_redeemed' => 'integer',
        'total_spent_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($card) {
            if (empty($card->card_number)) {
                $card->card_number = static::generateCardNumber();
            }

            if (empty($card->issued_at)) {
                $card->issued_at = now();
            }
        });
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FidelityTransaction::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(FidelityTransaction::class)->where('type', 'points');
    }

    public function redemptionTransactions(): HasMany
    {
        return $this->hasMany(FidelityTransaction::class)->where('type', 'redemption');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCardNumber($query, string $cardNumber)
    {
        return $query->where('card_number', $cardNumber);
    }

    // Methods
    public static function generateCardNumber(): string
    {
        $config = config('cartino.fidelity.card');
        $prefix = $config['prefix'] ?? 'FID';
        $length = $config['length'] ?? 8;
        $separator = $config['separator'] ?? '-';

        do {
            $number = $prefix.$separator.strtoupper(Str::random($length));
        } while (static::where('card_number', $number)->exists());

        return $number;
    }

    public function addPoints(int $points, ?string $reason = null, ?int $orderId = null): FidelityTransaction
    {
        $transaction = $this->transactions()->create([
            'type' => 'earned',
            'points' => $points,
            'description' => $reason ?? 'Points earned',
            'order_id' => $orderId,
            'expires_at' => $this->calculatePointsExpiration(),
        ]);

        $this->increment('total_points', $points);
        $this->increment('available_points', $points);
        $this->increment('total_earned', $points);
        $this->update(['last_activity_at' => now()]);

        return $transaction;
    }

    public function redeemPoints(int $points, ?string $reason = null, ?int $orderId = null): FidelityTransaction
    {
        if ($points > $this->available_points) {
            throw new \InvalidArgumentException('Insufficient points for redemption.');
        }

        $transaction = $this->transactions()->create([
            'type' => 'redeemed',
            'points' => -$points,
            'description' => $reason ?? 'Points redeemed',
            'order_id' => $orderId,
        ]);

        $this->decrement('available_points', $points);
        $this->increment('total_redeemed', $points);
        $this->update(['last_activity_at' => now()]);

        return $transaction;
    }

    public function calculatePointsForAmount(float $amount, ?string $currency = null): int
    {
        $config = config('cartino.fidelity.points');

        if (! $config['enabled']) {
            return 0;
        }

        $baseCurrency = $config['currency_base'] ?? 'EUR';
        $convertedAmount = $this->convertCurrency($amount, $currency ?? $baseCurrency, $baseCurrency);

        $totalSpent = $this->total_spent_amount + $convertedAmount;
        $tier = $this->getTierForAmount($totalSpent);

        return (int) floor($convertedAmount * $tier);
    }

    public function getTierForAmount(float $totalSpent): float
    {
        $tiers = config('cartino.fidelity.points.conversion_rules.tiers', [0 => 1]);

        $applicableTier = 1;
        foreach ($tiers as $threshold => $rate) {
            if ($totalSpent >= $threshold) {
                $applicableTier = $rate;
            }
        }

        return $applicableTier;
    }

    public function getCurrentTier(): array
    {
        $tiers = config('cartino.fidelity.points.conversion_rules.tiers', [0 => 1]);
        $currentRate = $this->getTierForAmount($this->total_spent_amount);

        foreach ($tiers as $threshold => $rate) {
            if ($rate === $currentRate) {
                return [
                    'threshold' => $threshold,
                    'rate' => $rate,
                ];
            }
        }

        return ['threshold' => 0, 'rate' => 1];
    }

    public function getNextTier(): ?array
    {
        $tiers = config('cartino.fidelity.points.conversion_rules.tiers', [0 => 1]);
        $currentRate = $this->getTierForAmount($this->total_spent_amount);

        $nextTier = null;
        foreach ($tiers as $threshold => $rate) {
            if ($rate > $currentRate && $threshold > $this->total_spent_amount) {
                $nextTier = [
                    'threshold' => $threshold,
                    'rate' => $rate,
                    'amount_needed' => $threshold - $this->total_spent_amount,
                ];
                break;
            }
        }

        return $nextTier;
    }

    public function getPointsValue(): float
    {
        $rate = config('cartino.fidelity.points.redemption.points_to_currency_rate', 0.01);

        return $this->available_points * $rate;
    }

    public function canRedeemPoints(int $points): bool
    {
        $minPoints = config('cartino.fidelity.points.redemption.min_points', 100);

        return $this->available_points >= $points && $points >= $minPoints;
    }

    protected function calculatePointsExpiration(): ?\DateTime
    {
        $config = config('cartino.fidelity.points.expiration');

        if (! $config['enabled']) {
            return null;
        }

        $months = $config['months'] ?? 12;

        return now()->addMonths($months);
    }

    protected function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        // Implementazione semplificata - in un caso reale si userebbe un servizio di conversione
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Implementare logica di conversione valuta qui se necessario
        // Per ora assumiamo che sia tutto nella stessa valuta
        return $amount;
    }

    public function expirePoints(): void
    {
        $expiredTransactions = $this->transactions()
            ->where('type', 'earned')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('expired', false)
            ->get();

        foreach ($expiredTransactions as $transaction) {
            $transaction->update(['expired' => true]);

            // Crea una transazione di scadenza
            $this->transactions()->create([
                'type' => 'expired',
                'points' => -$transaction->points,
                'description' => 'Points expired',
                'reference_transaction_id' => $transaction->id,
            ]);

            $this->decrement('available_points', $transaction->points);
        }

        if ($expiredTransactions->count() > 0) {
            $this->update(['last_activity_at' => now()]);
        }
    }
}
