<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Support\HasHandle;
use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $company_number
 * @property string $name
 * @property string $handle
 * @property string|null $legal_name
 * @property string|null $vat_number
 * @property string|null $tax_id
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property int|null $parent_company_id
 * @property string $type
 * @property string $status
 * @property float|null $credit_limit
 * @property float $outstanding_balance
 * @property int|null $payment_terms_days
 * @property string|null $payment_method
 * @property float|null $approval_threshold
 * @property bool $requires_approval
 * @property string|null $risk_level
 * @property float|null $lifetime_value
 * @property int $order_count
 * @property Carbon|null $last_order_at
 * @property bool $tax_exempt
 * @property array|null $tax_exemptions
 * @property array|null $billing_address
 * @property array|null $shipping_address
 * @property string|null $notes
 * @property array|null $settings
 * @property array|null $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Company extends Model
{
    use HasCustomFields;
    use HasFactory;
    use HasHandle;
    use HasOptimizedFilters;
    use SoftDeletes;

    protected $fillable = [
        'company_number',
        'name',
        'handle',
        'legal_name',
        'vat_number',
        'tax_id',
        'email',
        'phone',
        'website',
        'parent_company_id',
        'type',
        'status',
        'credit_limit',
        'outstanding_balance',
        'payment_terms_days',
        'payment_method',
        'approval_threshold',
        'requires_approval',
        'risk_level',
        'lifetime_value',
        'order_count',
        'last_order_at',
        'tax_exempt',
        'tax_exemptions',
        'billing_address',
        'shipping_address',
        'notes',
        'settings',
        'data',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'approval_threshold' => 'decimal:2',
        'requires_approval' => 'boolean',
        'lifetime_value' => 'decimal:2',
        'order_count' => 'integer',
        'last_order_at' => 'datetime',
        'tax_exempt' => 'boolean',
        'tax_exemptions' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'settings' => 'array',
        'data' => 'array',
    ];

    protected $attributes = [
        'type' => 'standard',
        'status' => 'active',
        'outstanding_balance' => 0,
        'order_count' => 0,
        'requires_approval' => false,
        'tax_exempt' => false,
        'risk_level' => 'low',
    ];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'company_number',
        'name',
        'handle',
        'vat_number',
        'tax_id',
        'email',
        'type',
        'status',
        'parent_company_id',
        'risk_level',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'company_number',
        'name',
        'created_at',
        'updated_at',
        'lifetime_value',
        'order_count',
        'last_order_at',
        'outstanding_balance',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($company) {
            if (! $company->company_number) {
                $company->company_number = static::generateCompanyNumber();
            }
        });
    }

    /**
     * Generate unique company number
     */
    protected static function generateCompanyNumber(): string
    {
        $prefix = config('cartino.company.number_prefix', 'COMP-');
        $lastCompany = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastCompany ? ($lastCompany->id + 1) : 1;

        return $prefix.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parent company relationship
     */
    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    /**
     * Subsidiary companies relationship
     */
    public function subsidiaries(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }

    /**
     * Users associated with this company
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot(['role', 'can_approve_orders', 'approval_limit', 'is_primary', 'status'])
            ->withTimestamps();
    }

    /**
     * Customers associated with this company
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'company_customer')
            ->withPivot(['role', 'is_primary', 'status'])
            ->withTimestamps();
    }

    /**
     * Orders placed by this company
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Order approvals for this company
     */
    public function orderApprovals(): HasMany
    {
        return $this->hasMany(OrderApproval::class);
    }

    /**
     * Scope: Active companies
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Suspended companies
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope: Companies by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Companies with credit limit
     */
    public function scopeWithCreditLimit($query)
    {
        return $query->whereNotNull('credit_limit')
            ->where('credit_limit', '>', 0);
    }

    /**
     * Scope: Companies approaching credit limit
     */
    public function scopeApproachingCreditLimit($query, int $percentage = 80)
    {
        return $query->whereNotNull('credit_limit')
            ->where('credit_limit', '>', 0)
            ->whereRaw('(outstanding_balance / credit_limit) * 100 >= ?', [$percentage]);
    }

    /**
     * Scope: Companies with high risk
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Check if company has available credit
     */
    public function hasAvailableCredit(float $amount): bool
    {
        if (! $this->credit_limit) {
            return true; // No credit limit set
        }

        return ($this->outstanding_balance + $amount) <= $this->credit_limit;
    }

    /**
     * Get available credit amount
     */
    public function getAvailableCredit(): float
    {
        if (! $this->credit_limit) {
            return PHP_FLOAT_MAX; // Unlimited
        }

        return max(0, $this->credit_limit - $this->outstanding_balance);
    }

    /**
     * Check if order requires approval
     */
    public function orderRequiresApproval(float $amount): bool
    {
        if (! $this->requires_approval) {
            return false;
        }

        if (! $this->approval_threshold) {
            return true; // Always require approval if enabled but no threshold set
        }

        return $amount >= $this->approval_threshold;
    }

    /**
     * Get company managers (users who can approve orders)
     */
    public function getManagers()
    {
        return $this->users()
            ->wherePivot('can_approve_orders', true)
            ->wherePivot('status', 'active')
            ->get();
    }

    /**
     * Attribute: Display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->legal_name ?? $this->name;
    }

    /**
     * Attribute: Is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Attribute: Credit utilization percentage
     */
    public function getCreditUtilizationAttribute(): float
    {
        if (! $this->credit_limit || $this->credit_limit <= 0) {
            return 0;
        }

        return round(($this->outstanding_balance / $this->credit_limit) * 100, 2);
    }

    /**
     * Route key name
     */
    public function getRouteKeyName(): string
    {
        return 'handle';
    }
}
