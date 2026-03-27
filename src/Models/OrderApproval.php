<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $company_id
 * @property int $requested_by_id
 * @property int|null $approver_id
 * @property string $status
 * @property float $order_total
 * @property float|null $approval_threshold
 * @property bool $threshold_exceeded
 * @property string|null $approval_reason
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $expires_at
 * @property array|null $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OrderApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_id',
        'requested_by_id',
        'approver_id',
        'status',
        'order_total',
        'approval_threshold',
        'threshold_exceeded',
        'approval_reason',
        'rejection_reason',
        'notes',
        'approved_at',
        'rejected_at',
        'expires_at',
        'data',
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'approval_threshold' => 'decimal:2',
        'threshold_exceeded' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'data' => 'array',
    ];

    protected $attributes = [
        'status' => 'pending',
        'threshold_exceeded' => false,
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($approval) {
            // Set default expiration (7 days)
            if (! $approval->expires_at) {
                $approval->expires_at = now()->addDays(
                    config('cartino.approval.expiration_days', 7)
                );
            }
        });
    }

    /**
     * Order relationship
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * User who requested approval
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    /**
     * User who approved/rejected
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope: Pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Approved
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Rejected
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Expired
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope: For company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: For approver
     */
    public function scopeForApprover($query, int $userId)
    {
        return $query->whereHas('company.users', function ($q) use ($userId) {
            $q->where('users.id', $userId)
                ->wherePivot('can_approve_orders', true)
                ->wherePivot('status', 'active');
        });
    }

    /**
     * Approve the order
     */
    public function approve(User $approver, ?string $reason = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approver_id' => $approver->id,
            'approval_reason' => $reason,
            'approved_at' => now(),
        ]);

        // Update order status
        $this->order->update([
            'status' => 'approved',
        ]);

        return true;
    }

    /**
     * Reject the order
     */
    public function reject(User $approver, string $reason): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);

        // Update order status
        $this->order->update([
            'status' => 'cancelled',
            'cancel_reason' => "Approval rejected: {$reason}",
        ]);

        return true;
    }

    /**
     * Check if approval is expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->status === 'pending' && $this->expires_at->isPast();
    }

    /**
     * Check if user can approve
     */
    public function canBeApprovedBy(User $user): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        // Check if user is a manager in the company
        return $this->company->users()
            ->where('users.id', $user->id)
            ->wherePivot('can_approve_orders', true)
            ->wherePivot('status', 'active')
            ->exists();
    }

    /**
     * Attribute: Is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    /**
     * Attribute: Is approved
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Attribute: Is rejected
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Attribute: Days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (! $this->expires_at || $this->status !== 'pending') {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }
}
