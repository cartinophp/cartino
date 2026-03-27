# Cartino - Recommended Implementations

Roadmap implementativa basata sull'analisi comparativa con PrestaShop, Shopware, Craft Commerce, Shopify e Sylius.

## 📋 Priority Levels

- 🔥 **CRITICAL** - Needed for enterprise/scalability (5M+ products)
- 🚀 **HIGH** - Significant competitive advantage
- 🟡 **MEDIUM** - Nice to have, industry standard
- 🟢 **LOW** - Future enhancement

---

## PHASE 1: PRODUCT & CATALOG 🔥 CRITICAL

### 1.1 Product Enhancements

#### Migration: Add Product Fields
```php
Schema::table('products', function (Blueprint $table) {
    // Permanent URL identifier
    $table->string('handle')->unique()->after('slug');

    // Pricing enhancements
    $table->decimal('compare_at_price', 10, 2)->nullable()->after('price');
    $table->decimal('cost_price', 10, 2)->nullable()->after('compare_at_price');

    // Inventory policies
    $table->enum('inventory_policy', ['deny', 'continue'])->default('deny')->after('track_inventory');
    $table->integer('min_order_quantity')->default(1)->after('inventory_policy');
    $table->integer('order_increment')->default(1)->after('min_order_quantity');
    $table->boolean('is_closeout')->default(false)->after('order_increment');
    $table->integer('restock_days')->nullable()->after('is_closeout');

    // Product info
    $table->enum('condition', ['new', 'used', 'refurbished'])->default('new')->after('restock_days');
    $table->string('hs_code', 20)->nullable()->after('condition');
    $table->string('country_of_origin', 2)->nullable()->after('hs_code');

    // Visibility
    $table->enum('visibility', ['everywhere', 'catalog', 'search', 'none'])->default('everywhere')->after('status');

    // Indexes
    $table->index('handle');
    $table->index('visibility');
    $table->index('is_closeout');
});
```

#### Migration: Product Bundles
```php
Schema::create('product_bundles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->foreignId('bundled_product_id')->constrained('products')->cascadeOnDelete();
    $table->integer('quantity')->default(1);
    $table->decimal('discount_percent', 5, 2)->nullable();
    $table->boolean('is_optional')->default(false);
    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'bundled_product_id']);
    $table->index(['product_id', 'sort_order']);
});
```

#### Migration: Product Relations
```php
Schema::create('product_relations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
    $table->enum('type', ['upsell', 'cross_sell', 'related', 'frequently_bought_together'])->default('related');
    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'related_product_id', 'type']);
    $table->index(['product_id', 'type', 'sort_order']);
});
```

#### Model: Product.php Additions
```php
class Product extends Model
{
    // Relationships
    public function bundles()
    {
        return $this->belongsToMany(Product::class, 'product_bundles', 'product_id', 'bundled_product_id')
            ->withPivot(['quantity', 'discount_percent', 'is_optional', 'sort_order'])
            ->orderBy('product_bundles.sort_order');
    }

    public function upsells()
    {
        return $this->relations()->where('type', 'upsell');
    }

    public function crossSells()
    {
        return $this->relations()->where('type', 'cross_sell');
    }

    public function relatedProducts()
    {
        return $this->relations()->where('type', 'related');
    }

    public function frequentlyBoughtTogether()
    {
        return $this->relations()->where('type', 'frequently_bought_together');
    }

    protected function relations()
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->withPivot(['type', 'sort_order'])
            ->orderBy('product_relations.sort_order');
    }

    // Accessors
    public function getHandleAttribute()
    {
        return $this->attributes['handle'] ?? $this->slug;
    }

    public function canSellWhenOutOfStock(): bool
    {
        return $this->inventory_policy === 'continue';
    }

    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->stock_quantity > 0 || $this->canSellWhenOutOfStock();
    }

    public function needsRestock(): bool
    {
        return $this->track_inventory &&
               $this->stock_quantity <= $this->low_stock_threshold &&
               !$this->is_closeout;
    }

    public function estimatedRestockDate(): ?Carbon
    {
        if (!$this->needsRestock() || !$this->restock_days) {
            return null;
        }

        return now()->addDays($this->restock_days);
    }
}
```

---

### 1.2 Digital Products

#### Migration: Product Downloads
```php
Schema::create('product_downloads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
    $table->string('name');
    $table->string('filename');
    $table->string('file_path');
    $table->bigInteger('file_size'); // bytes
    $table->string('mime_type');
    $table->string('file_hash', 64); // SHA-256
    $table->integer('download_limit')->nullable(); // null = unlimited
    $table->integer('download_expiry_days')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['product_id', 'is_active']);
});

Schema::create('order_downloads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->foreignId('product_download_id')->constrained('product_downloads')->cascadeOnDelete();
    $table->string('download_token', 64)->unique();
    $table->integer('downloads_remaining')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('first_downloaded_at')->nullable();
    $table->timestamp('last_downloaded_at')->nullable();
    $table->integer('download_count')->default(0);
    $table->timestamps();

    $table->index(['order_id', 'expires_at']);
    $table->index('download_token');
});
```

---

## PHASE 2: ADVANCED PRICING 🔥 CRITICAL

### 2.1 Price Rules Engine

#### Migration: Price Rules
```php
Schema::create('price_rules', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('priority')->default(0); // Higher = evaluated first

    // Applicability
    $table->enum('entity_type', ['product', 'variant', 'category', 'cart'])->default('product');
    $table->json('entity_ids')->nullable(); // null = all

    // Conditions (JSONB)
    $table->jsonb('conditions')->nullable();
    // {
    //   customer_group_ids: [1, 2],
    //   customer_ids: [10],
    //   channel_ids: [1],
    //   site_ids: [1],
    //   country_ids: ['IT', 'US'],
    //   zone_ids: [1],
    //   min_cart_value: 100,
    //   min_quantity: 5,
    //   product_attributes: {brand_id: 5},
    //   custom_conditions: []
    // }

    // Actions
    $table->enum('discount_type', ['percent', 'fixed', 'override'])->default('percent');
    $table->decimal('discount_value', 10, 4);
    $table->boolean('stop_further_rules')->default(false);

    // Time-based
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('ends_at')->nullable();

    // Usage limits
    $table->integer('usage_limit')->nullable(); // total uses
    $table->integer('usage_limit_per_customer')->nullable();
    $table->integer('usage_count')->default(0);

    $table->timestamps();
    $table->softDeletes();

    $table->index(['is_active', 'priority']);
    $table->index(['starts_at', 'ends_at']);
});

Schema::create('price_rule_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('price_rule_id')->constrained('price_rules')->cascadeOnDelete();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
    $table->decimal('discount_amount', 10, 2);
    $table->timestamps();

    $table->index(['price_rule_id', 'customer_id']);
    $table->index('order_id');
});
```

#### Service: PriceCalculator
```php
namespace Cartino\Services;

class PriceCalculator
{
    public function calculatePrice(
        ProductVariant $variant,
        ?Customer $customer = null,
        ?Channel $channel = null,
        int $quantity = 1,
        ?Cart $cart = null
    ): PriceResult {
        $basePrice = $this->getBasePrice($variant, $customer, $channel);

        // Apply price rules
        $rules = $this->getApplicableRules($variant, $customer, $channel, $quantity, $cart);

        $finalPrice = $basePrice;
        $appliedRules = [];

        foreach ($rules as $rule) {
            $discountedPrice = $this->applyRule($finalPrice, $rule);

            if ($discountedPrice < $finalPrice) {
                $finalPrice = $discountedPrice;
                $appliedRules[] = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'discount' => $basePrice - $finalPrice,
                ];

                if ($rule->stop_further_rules) {
                    break;
                }
            }
        }

        return new PriceResult([
            'base_price' => $basePrice,
            'final_price' => $finalPrice,
            'discount' => $basePrice - $finalPrice,
            'applied_rules' => $appliedRules,
        ]);
    }

    protected function getApplicableRules(...$params): Collection
    {
        return PriceRule::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->get()
            ->filter(function ($rule) use ($params) {
                return $this->matchesConditions($rule, ...$params);
            });
    }

    protected function matchesConditions(PriceRule $rule, ...$params): bool
    {
        // Implement complex condition matching
        // Check customer groups, channels, countries, min quantity, etc.
        return true; // Simplified
    }

    protected function applyRule(float $price, PriceRule $rule): float
    {
        return match ($rule->discount_type) {
            'percent' => $price * (1 - $rule->discount_value / 100),
            'fixed' => max(0, $price - $rule->discount_value),
            'override' => $rule->discount_value,
        };
    }
}
```

---

## PHASE 3: INVENTORY MANAGEMENT 🚀 HIGH

### 3.1 Multi-Warehouse Stock

#### Migration: Warehouses
```php
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code', 20)->unique();
    $table->enum('type', ['warehouse', 'store', 'dropship', 'supplier'])->default('warehouse');
    $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_primary')->default(false);
    $table->integer('priority')->default(0); // For fulfillment priority
    $table->json('settings')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['is_active', 'priority']);
});

Schema::create('stock_levels', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

    // Stock quantities
    $table->integer('quantity_on_hand')->default(0);
    $table->integer('quantity_reserved')->default(0);
    $table->integer('quantity_incoming')->default(0); // From POs
    $table->integer('quantity_damaged')->default(0);

    // Computed: quantity_available = on_hand - reserved
    // $table->integer('quantity_available')->storedAs('quantity_on_hand - quantity_reserved');

    // Reorder point
    $table->integer('reorder_point')->nullable();
    $table->integer('reorder_quantity')->nullable();

    // Cost tracking
    $table->decimal('cost_price', 10, 2)->nullable();

    $table->timestamps();

    $table->unique(['product_variant_id', 'warehouse_id']);
    $table->index(['warehouse_id', 'quantity_on_hand']);
});

Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stock_level_id')->constrained('stock_levels')->cascadeOnDelete();
    $table->integer('quantity_delta'); // Can be negative
    $table->enum('type', [
        'purchase',      // From supplier
        'sale',          // To customer
        'return',        // Customer return
        'adjustment',    // Manual adjustment
        'transfer_in',   // From another warehouse
        'transfer_out',  // To another warehouse
        'damaged',       // Mark as damaged
        'found',         // Stock count found
        'lost',          // Stock count lost
    ]);
    $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
    $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->cascadeOnDelete();
    $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->cascadeOnDelete();
    $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['stock_level_id', 'created_at']);
    $table->index('type');
});

Schema::create('stock_reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stock_level_id')->constrained('stock_levels')->cascadeOnDelete();
    $table->integer('quantity');
    $table->morphs('reservable'); // Order, Cart
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();

    $table->index(['stock_level_id', 'expires_at']);
    $table->index(['reservable_type', 'reservable_id']);
});
```

#### Model: StockLevel
```php
class StockLevel extends Model
{
    public function getQuantityAvailableAttribute(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    public function reserve(int $quantity, $reservable, ?Carbon $expiresAt = null): StockReservation
    {
        if ($quantity > $this->quantity_available) {
            throw new InsufficientStockException();
        }

        $reservation = $this->reservations()->create([
            'quantity' => $quantity,
            'reservable_type' => get_class($reservable),
            'reservable_id' => $reservable->id,
            'expires_at' => $expiresAt ?? now()->addHours(2),
        ]);

        $this->increment('quantity_reserved', $quantity);

        return $reservation;
    }

    public function release(StockReservation $reservation): void
    {
        $this->decrement('quantity_reserved', $reservation->quantity);
        $reservation->delete();
    }

    public function fulfill(int $quantity): void
    {
        if ($quantity > $this->quantity_on_hand) {
            throw new InsufficientStockException();
        }

        $this->decrement('quantity_on_hand', $quantity);
        $this->decrement('quantity_reserved', $quantity);

        $this->movements()->create([
            'quantity_delta' => -$quantity,
            'type' => 'sale',
        ]);
    }
}
```

---

## PHASE 4: ORDER WORKFLOW 🔥 CRITICAL

### 4.1 Order States & History

#### Migration: Order States
```php
Schema::create('order_states', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name');
    $table->string('color', 7)->default('#6b7280'); // Hex color for UI
    $table->text('description')->nullable();

    // State flags
    $table->boolean('is_paid')->default(false);
    $table->boolean('is_shipped')->default(false);
    $table->boolean('is_delivered')->default(false);
    $table->boolean('is_cancelled')->default(false);
    $table->boolean('is_refunded')->default(false);
    $table->boolean('is_final')->default(false); // No further changes

    // Notifications
    $table->boolean('send_email')->default(false);
    $table->string('email_template')->nullable();
    $table->boolean('send_sms')->default(false);

    // Permissions
    $table->boolean('customer_can_view')->default(true);
    $table->boolean('customer_can_cancel')->default(false);

    $table->integer('sort_order')->default(0);
    $table->timestamps();

    $table->index('sort_order');
});

Schema::create('order_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->foreignId('from_state_id')->nullable()->constrained('order_states')->nullOnDelete();
    $table->foreignId('to_state_id')->constrained('order_states')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->text('notes')->nullable();
    $table->json('metadata')->nullable(); // Additional context
    $table->timestamps();

    $table->index(['order_id', 'created_at']);
});

Schema::table('orders', function (Blueprint $table) {
    $table->foreignId('state_id')->after('status')->constrained('order_states');
    $table->boolean('is_test')->default(false)->after('state_id');
    $table->timestamp('confirmed_at')->nullable()->after('is_test');
    $table->timestamp('processed_at')->nullable()->after('confirmed_at');
    $table->timestamp('cancelled_at')->nullable()->after('processed_at');
    $table->string('cancel_reason')->nullable()->after('cancelled_at');
    $table->enum('risk_level', ['low', 'medium', 'high'])->nullable()->after('cancel_reason');
    $table->text('risk_message')->nullable()->after('risk_level');
    $table->enum('source', ['web', 'mobile', 'pos', 'api', 'manual'])->default('web')->after('risk_message');
    $table->foreignId('cart_id')->nullable()->after('source')->constrained('carts')->nullOnDelete();
    $table->json('customer_snapshot')->nullable()->after('cart_id');
    $table->text('customer_note')->nullable()->after('customer_snapshot');
    $table->text('merchant_note')->nullable()->after('customer_note');
    $table->json('tags')->nullable()->after('merchant_note');
    $table->json('custom_attributes')->nullable()->after('tags');

    $table->index('state_id');
    $table->index('is_test');
    $table->index('risk_level');
    $table->index('source');
});
```

#### Migration: Order Fulfillments
```php
Schema::create('order_fulfillments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->string('fulfillment_number')->unique();
    $table->enum('status', ['pending', 'processing', 'shipped', 'in_transit', 'delivered', 'failed'])->default('pending');
    $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
    $table->foreignId('carrier_id')->nullable()->constrained('couriers')->nullOnDelete();
    $table->string('tracking_number')->nullable();
    $table->string('tracking_url')->nullable();
    $table->json('items'); // [{order_line_id, quantity}]
    $table->text('notes')->nullable();
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('in_transit_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamp('failed_at')->nullable();
    $table->timestamps();

    $table->index(['order_id', 'status']);
    $table->index('tracking_number');
});

Schema::create('order_returns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->string('return_number')->unique();
    $table->enum('status', ['requested', 'approved', 'rejected', 'received', 'refunded'])->default('requested');
    $table->enum('reason', ['damaged', 'defective', 'wrong_item', 'not_as_described', 'unwanted', 'other'])->nullable();
    $table->text('reason_details')->nullable();
    $table->json('items'); // [{order_line_id, quantity, condition}]
    $table->decimal('refund_amount', 10, 2);
    $table->boolean('restock')->default(true);
    $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
    $table->text('customer_notes')->nullable();
    $table->text('merchant_notes')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('received_at')->nullable();
    $table->timestamp('refunded_at')->nullable();
    $table->timestamps();

    $table->index(['order_id', 'status']);
    $table->index('return_number');
});
```

#### Seeder: Default Order States
```php
DB::table('order_states')->insert([
    [
        'code' => 'pending',
        'name' => 'Pending Payment',
        'color' => '#f59e0b',
        'is_paid' => false,
        'send_email' => true,
        'email_template' => 'order_pending',
        'customer_can_cancel' => true,
        'sort_order' => 1,
    ],
    [
        'code' => 'paid',
        'name' => 'Paid',
        'color' => '#10b981',
        'is_paid' => true,
        'send_email' => true,
        'email_template' => 'order_paid',
        'sort_order' => 2,
    ],
    [
        'code' => 'processing',
        'name' => 'Processing',
        'color' => '#3b82f6',
        'is_paid' => true,
        'send_email' => false,
        'sort_order' => 3,
    ],
    [
        'code' => 'shipped',
        'name' => 'Shipped',
        'color' => '#8b5cf6',
        'is_shipped' => true,
        'send_email' => true,
        'email_template' => 'order_shipped',
        'sort_order' => 4,
    ],
    [
        'code' => 'delivered',
        'name' => 'Delivered',
        'color' => '#059669',
        'is_delivered' => true,
        'is_final' => true,
        'send_email' => true,
        'email_template' => 'order_delivered',
        'sort_order' => 5,
    ],
    [
        'code' => 'cancelled',
        'name' => 'Cancelled',
        'color' => '#dc2626',
        'is_cancelled' => true,
        'is_final' => true,
        'send_email' => true,
        'email_template' => 'order_cancelled',
        'sort_order' => 98,
    ],
    [
        'code' => 'refunded',
        'name' => 'Refunded',
        'color' => '#dc2626',
        'is_refunded' => true,
        'is_final' => true,
        'send_email' => true,
        'email_template' => 'order_refunded',
        'sort_order' => 99,
    ],
]);
```

---

## PHASE 5: CUSTOMER ENHANCEMENTS 🟡 MEDIUM

### 5.1 Customer B2B Features

#### Migration: Customer Enhancements
```php
Schema::table('customers', function (Blueprint $table) {
    // Unique identifiers
    $table->string('customer_number', 50)->unique()->nullable()->after('id');
    $table->string('handle')->unique()->nullable()->after('customer_number');

    // State management
    $table->enum('state', ['active', 'disabled', 'invited', 'declined'])->default('active')->after('email');
    $table->timestamp('email_verified_at')->nullable()->after('state');
    $table->timestamp('invited_at')->nullable()->after('email_verified_at');

    // B2B fields
    $table->string('company_name')->nullable()->after('last_name');
    $table->string('vat_number', 30)->nullable()->after('company_name');
    $table->string('tax_id', 30)->nullable()->after('vat_number');
    $table->boolean('tax_exempt')->default(false)->after('tax_id');
    $table->json('tax_exemptions')->nullable()->after('tax_exempt'); // ['IT_VAT', 'US_SALES_TAX']

    // Credit management
    $table->decimal('credit_limit', 10, 2)->default(0)->after('tax_exemptions');
    $table->decimal('outstanding_balance', 10, 2)->default(0)->after('credit_limit');

    // Risk & fraud
    $table->enum('risk_level', ['low', 'medium', 'high'])->default('low')->after('outstanding_balance');

    // Cached aggregates (for performance)
    $table->timestamp('last_order_at')->nullable()->after('risk_level');
    $table->decimal('lifetime_value', 10, 2)->default(0)->after('last_order_at');
    $table->integer('order_count')->default(0)->after('lifetime_value');

    // Marketing consent
    $table->timestamp('marketing_consent_at')->nullable()->after('accepts_marketing');
    $table->boolean('sms_marketing_consent')->default(false)->after('marketing_consent_at');
    $table->timestamp('sms_marketing_consent_at')->nullable()->after('sms_marketing_consent');
    $table->enum('marketing_opt_in_level', ['single', 'confirmed', 'unknown'])->default('unknown')->after('sms_marketing_consent_at');

    // Notes
    $table->text('merchant_notes')->nullable()->after('marketing_opt_in_level');

    // Indexes
    $table->index('customer_number');
    $table->index('state');
    $table->index('tax_exempt');
    $table->index('risk_level');
});

// Multiple customer groups
Schema::create('customer_customer_group', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
    $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();
    $table->boolean('is_primary')->default(false);
    $table->timestamps();

    $table->unique(['customer_id', 'customer_group_id']);
    $table->index(['customer_group_id', 'is_primary']);
});
```

---

## IMPLEMENTATION ROADMAP

### Phase 1: Foundation (Month 1-2)
- [ ] Product enhancements (handle, compare_at_price, inventory_policy)
- [ ] Product bundles
- [ ] Product relations
- [ ] Price rules engine
- [ ] Order states workflow

### Phase 2: Inventory (Month 2-3)
- [ ] Multi-warehouse structure
- [ ] Stock levels tracking
- [ ] Stock movements
- [ ] Stock reservations
- [ ] Integrate with order workflow

### Phase 3: Orders (Month 3-4)
- [ ] Order fulfillments
- [ ] Order returns/RMA
- [ ] Multiple payments support
- [ ] Order timeline/history

### Phase 4: Customer B2B (Month 4-5)
- [ ] Customer enhancements
- [ ] Multiple customer groups
- [ ] Credit management
- [ ] Customer approval workflow

### Phase 5: Channel Management (Month 5-6)
- [ ] Channel-specific pricing
- [ ] Channel-specific inventory
- [ ] Product publications
- [ ] Channel domains

---

Vuoi che generi le migration complete per uno di questi moduli? O preferisci che approfondisca una specifica area?
