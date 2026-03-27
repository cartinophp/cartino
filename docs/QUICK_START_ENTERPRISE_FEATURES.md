# Quick Start - Enterprise Features

Guida rapida per utilizzare le nuove funzionalità enterprise implementate per Cartino.

---

## 🚀 Installazione

### 1. Esegui le Migrations

```bash
# Run all new migrations
php artisan migrate

# Se hai errori, check i prerequisiti:
# - Tabella 'addresses' deve esistere
# - Tabella 'couriers' deve esistere
# - Tabella 'purchase_orders' deve esistere
```

**Migrations in Ordine**:
1. ✅ `2025_01_01_000007_add_product_enhancements.php`
2. ✅ `2025_01_01_000008_create_product_bundles_table.php`
3. ✅ `2025_01_01_000009_create_product_relations_table.php`
4. ✅ `2025_01_01_000010_create_price_rules_table.php`
5. ✅ `2025_01_01_000011_create_order_states_system.php` (seeds 8 stati)
6. ✅ `2025_01_01_000012_create_order_fulfillments_system.php`
7. ✅ `2025_01_01_000013_create_multi_warehouse_system.php`
8. ✅ `2025_01_01_000014_add_customer_b2b_enhancements.php`

### 2. Verifica Seeding

```bash
# Gli order states vengono seeded automaticamente
php artisan tinker
>>> \Cartino\Models\OrderState::count()
# Dovrebbe ritornare 8
```

---

## 📦 Esempi di Utilizzo Rapidi

### 1. Product Bundles

```php
use Cartino\Models\Product;

// Create un bundle
$bundle = Product::create([
    'title' => 'Gaming Setup Complete',
    'slug' => 'gaming-setup-complete',
    'product_type' => 'bundle',
    'price' => 0, // Calculated
]);

// Aggiungi prodotti al bundle
$bundle->bundles()->attach([
    100 => [  // Gaming PC
        'quantity' => 1,
        'discount_percent' => 0,
        'is_optional' => false,
        'sort_order' => 1,
    ],
    101 => [  // Gaming Mouse
        'quantity' => 1,
        'discount_percent' => 10,
        'is_optional' => false,
        'sort_order' => 2,
    ],
    102 => [  // Gaming Headset
        'quantity' => 1,
        'discount_percent' => 10,
        'is_optional' => true,  // Cliente può rimuoverlo
        'sort_order' => 3,
    ],
]);

// Frontend: Mostra bundle
$bundle->bundles->each(function ($product) {
    echo "{$product->pivot->quantity}x {$product->title}";

    if ($product->pivot->discount_percent > 0) {
        echo " (-{$product->pivot->discount_percent}%)";
    }

    if ($product->pivot->is_optional) {
        echo " [Opzionale]";
    }

    echo "\n";
});

// Find bundles che contengono un prodotto
$mouse = Product::find(101);
$bundles = $mouse->bundledIn; // Tutti i bundle che includono questo mouse
```

### 2. Product Relations

```php
$laptop = Product::find(1);

// Aggiungi upsells (alternative più costose)
$laptop->upsells()->attach([
    2 => ['sort_order' => 1],  // Laptop Pro
    3 => ['sort_order' => 2],  // Laptop Ultra
]);

// Aggiungi cross-sells (accessori)
$laptop->crossSells()->attach([
    10 => ['sort_order' => 1],  // Laptop Bag
    11 => ['sort_order' => 2],  // Mouse
    12 => ['sort_order' => 3],  // External Monitor
]);

// Aggiungi related (simili)
$laptop->relatedProducts()->attach([20, 21, 22]);

// Aggiungi frequently bought together
$laptop->frequentlyBoughtTogether()->attach([30, 31]);

// Frontend: Product Detail Page
echo "Upsells:\n";
foreach ($laptop->upsells as $upsell) {
    echo "- {$upsell->title} (€{$upsell->price})\n";
}

echo "\nAccessories:\n";
foreach ($laptop->crossSells as $accessory) {
    echo "- {$accessory->title}\n";
}
```

### 3. Price Rules

```php
use Cartino\Models\PriceRule;

// VIP Discount: 15% off everything
PriceRule::create([
    'name' => 'VIP Customer Discount',
    'description' => 'VIP customers get 15% off all products',
    'is_active' => true,
    'priority' => 100,  // Higher priority
    'entity_type' => 'product',
    'entity_ids' => null,  // Applies to ALL products
    'conditions' => [
        'customer_group_ids' => [5],  // VIP group ID
    ],
    'discount_type' => 'percent',
    'discount_value' => 15,
    'stop_further_rules' => false,
]);

// Bulk Discount: Buy 10+, get 20% off
PriceRule::create([
    'name' => 'Bulk Purchase Discount',
    'priority' => 90,
    'entity_type' => 'product',
    'conditions' => [
        'min_quantity' => 10,
    ],
    'discount_type' => 'percent',
    'discount_value' => 20,
]);

// Black Friday: 30% off Gaming category, time-limited
PriceRule::create([
    'name' => 'Black Friday - Gaming',
    'priority' => 80,
    'entity_type' => 'category',
    'entity_ids' => [5],  // Gaming category ID
    'discount_type' => 'percent',
    'discount_value' => 30,
    'starts_at' => '2025-11-29 00:00:00',
    'ends_at' => '2025-11-30 23:59:59',
    'usage_limit' => 1000,  // Max 1000 uses
]);

// B2B Italy: €10 off for Italian B2B customers on orders €100+
PriceRule::create([
    'name' => 'B2B Italy Discount',
    'priority' => 70,
    'entity_type' => 'cart',
    'conditions' => [
        'customer_group_ids' => [3],  // B2B group
        'country_ids' => ['IT'],
        'min_cart_value' => 100,
    ],
    'discount_type' => 'fixed',
    'discount_value' => 10,
]);

// Calculate price with rules
use Cartino\Services\PriceCalculator;

$calculator = app(PriceCalculator::class);

$result = $calculator->calculatePrice(
    variant: $variant,
    customer: $customer,
    channel: $channel,
    quantity: 15,
    cart: $customer->cart
);

/*
$result = [
    'base_price' => 100.00,
    'final_price' => 68.00,
    'discount' => 32.00,
    'applied_rules' => [
        ['rule_name' => 'VIP Customer Discount', 'discount_amount' => 15.00],
        ['rule_name' => 'Bulk Purchase Discount', 'discount_amount' => 17.00],
    ],
    'currency' => 'EUR',
]
*/
```

### 4. Order States & Workflow

```php
use Cartino\Models\Order;
use Cartino\Models\OrderState;

// Create order con stato iniziale
$order = Order::create([
    'customer_id' => $customer->id,
    'state_id' => OrderState::where('code', 'pending')->first()->id,
    'total' => 150.00,
    'source' => 'web',
    'customer_note' => 'Please deliver before 5pm',
]);

// Transizione a "paid" dopo pagamento
$paidState = OrderState::where('code', 'paid')->first();

$order->histories()->create([
    'from_state_id' => $order->state_id,
    'to_state_id' => $paidState->id,
    'user_id' => auth()->id(),
    'notes' => 'Payment received via Stripe',
    'metadata' => [
        'payment_id' => 'pi_abc123',
        'payment_method' => 'card',
    ],
]);

$order->update([
    'state_id' => $paidState->id,
    'confirmed_at' => now(),
]);

// Se lo stato richiede email, invia
if ($paidState->send_email) {
    Mail::to($order->customer->email)
        ->send(new OrderStateChanged($order, $paidState));
}

// View state history
foreach ($order->histories()->with(['fromState', 'toState', 'user'])->get() as $history) {
    echo "[{$history->created_at}] ";
    echo "{$history->fromState?->name ?? 'Created'} → {$history->toState->name}";
    if ($history->user) {
        echo " by {$history->user->name}";
    }
    if ($history->notes) {
        echo " - {$history->notes}";
    }
    echo "\n";
}

// Custom states (oltre ai default)
OrderState::create([
    'code' => 'awaiting_stock',
    'name' => 'Awaiting Stock',
    'color' => '#ff9800',
    'is_paid' => true,
    'send_email' => true,
    'email_template' => 'order_awaiting_stock',
    'sort_order' => 25,
]);
```

### 5. Multi-Warehouse Inventory

```php
use Cartino\Models\Warehouse;
use Cartino\Models\StockLevel;
use Cartino\Models\ProductVariant;

// Create warehouses
$whNyc = Warehouse::create([
    'name' => 'New York Warehouse',
    'code' => 'WH-NYC',
    'type' => 'warehouse',
    'is_active' => true,
    'is_primary' => true,
    'priority' => 100,
]);

$whLa = Warehouse::create([
    'name' => 'Los Angeles Warehouse',
    'code' => 'WH-LA',
    'type' => 'warehouse',
    'is_active' => true,
    'priority' => 90,
]);

// Set stock levels per variant per warehouse
$variant = ProductVariant::find(1);

$stockNyc = StockLevel::create([
    'product_variant_id' => $variant->id,
    'warehouse_id' => $whNyc->id,
    'quantity_on_hand' => 100,
    'quantity_reserved' => 20,
    'reorder_point' => 30,
    'reorder_quantity' => 50,
    'cost_price' => 45.00,
]);

$stockLa = StockLevel::create([
    'product_variant_id' => $variant->id,
    'warehouse_id' => $whLa->id,
    'quantity_on_hand' => 50,
    'quantity_reserved' => 5,
]);

// Check total available stock
$totalAvailable = $variant->stockLevels()
    ->sum(\DB::raw('quantity_on_hand - quantity_reserved'));
// 100-20 + 50-5 = 125 units available

// Reserve stock for cart
$cart = $customer->cart;
$reservation = $stockNyc->reservations()->create([
    'quantity' => 3,
    'reservable_type' => get_class($cart),
    'reservable_id' => $cart->id,
    'expires_at' => now()->addHours(2),
]);

// Update reservation count
$stockNyc->increment('quantity_reserved', 3);

// Record stock movement
$stockNyc->movements()->create([
    'quantity_delta' => -3,
    'type' => 'sale',
    'order_id' => $order->id,
    'user_id' => auth()->id(),
    'notes' => 'Sold to customer',
]);

$stockNyc->decrement('quantity_on_hand', 3);

// Transfer stock between warehouses
$stockNyc->movements()->create([
    'quantity_delta' => -10,
    'type' => 'transfer_out',
    'from_warehouse_id' => $whNyc->id,
    'to_warehouse_id' => $whLa->id,
    'user_id' => auth()->id(),
    'notes' => 'Rebalancing inventory',
]);

$stockLa->movements()->create([
    'quantity_delta' => 10,
    'type' => 'transfer_in',
    'from_warehouse_id' => $whNyc->id,
    'to_warehouse_id' => $whLa->id,
]);

// Auto-release expired reservations (cronjob)
\Artisan::command('reservations:release', function () {
    $expired = \Cartino\Models\StockReservation::where('expires_at', '<', now())->get();

    foreach ($expired as $reservation) {
        $reservation->stockLevel->decrement('quantity_reserved', $reservation->quantity);
        $reservation->delete();
    }

    $this->info("Released {$expired->count()} expired reservations");
})->purpose('Release expired stock reservations');
```

### 6. Order Fulfillments

```php
use Cartino\Models\OrderFulfillment;

// Create partial fulfillment (ship some items now)
$fulfillment = OrderFulfillment::create([
    'order_id' => $order->id,
    'fulfillment_number' => 'FUL-' . date('Y') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
    'status' => 'pending',
    'warehouse_id' => $whNyc->id,
    'carrier_id' => $dhl->id,
    'items' => [
        ['order_line_id' => 1, 'quantity' => 2],  // Ship 2 of item 1
        ['order_line_id' => 2, 'quantity' => 1],  // Ship 1 of item 2
    ],
]);

// Mark as shipped
$fulfillment->update([
    'status' => 'shipped',
    'tracking_number' => 'DHL1234567890',
    'tracking_url' => 'https://dhl.com/track/DHL1234567890',
    'shipped_at' => now(),
]);

// Update order line quantities
foreach ($fulfillment->items as $item) {
    \Cartino\Models\OrderLine::find($item['order_line_id'])
        ->increment('quantity_fulfilled', $item['quantity']);
}

// Send shipping email
Mail::to($order->customer->email)
    ->send(new OrderShipped($order, $fulfillment));

// Create return/RMA
$return = $order->returns()->create([
    'return_number' => 'RET-' . date('Y') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
    'status' => 'requested',
    'reason' => 'defective',
    'reason_details' => 'Screen has dead pixels',
    'items' => [
        ['order_line_id' => 1, 'quantity' => 1, 'condition' => 'defective'],
    ],
    'refund_amount' => 99.99,
    'restock' => false,  // Don't restock defective items
    'customer_notes' => 'Screen stopped working after 2 days',
]);

// Merchant approves return
$return->update([
    'status' => 'approved',
    'approved_at' => now(),
    'merchant_notes' => 'Approved - will issue refund upon receipt',
]);

// Mark as received and refunded
$return->update([
    'status' => 'refunded',
    'received_at' => now()->subDay(),
    'refunded_at' => now(),
]);

// Update order line
\Cartino\Models\OrderLine::find(1)
    ->increment('quantity_returned', 1);
```

### 7. B2B Customers

```php
use Cartino\Models\Customer;

// Create B2B customer
$b2bCustomer = Customer::create([
    'customer_number' => 'CUST-' . str_pad(1, 6, '0', STR_PAD_LEFT),
    'email' => 'purchasing@acmecorp.com',
    'first_name' => 'John',
    'last_name' => 'Smith',
    'state' => 'active',

    // B2B info
    'company_name' => 'Acme Corporation',
    'vat_number' => 'IT12345678901',
    'tax_id' => 'IT12345678901',
    'tax_exempt' => true,
    'tax_exemptions' => ['IT_VAT', 'EU_VAT'],

    // Credit
    'credit_limit' => 50000.00,
    'outstanding_balance' => 12500.00,

    // Risk
    'risk_level' => 'low',
]);

// Assign to customer groups
$b2bCustomer->customerGroups()->attach([
    3 => ['is_primary' => true],   // B2B group
    5 => ['is_primary' => false],  // VIP group
]);

// Add tags
$b2bCustomer->tags()->attach([1, 2, 5]);  // ['wholesale', 'priority', 'italy']

// Before placing order, check credit
$availableCredit = $b2bCustomer->credit_limit - $b2bCustomer->outstanding_balance;
// €50,000 - €12,500 = €37,500

if ($order->total > $availableCredit) {
    throw new \Exception("Insufficient credit. Available: €{$availableCredit}");
}

// After order confirmed
$b2bCustomer->increment('outstanding_balance', $order->total);
$b2bCustomer->increment('order_count');
$b2bCustomer->increment('lifetime_value', $order->total);
$b2bCustomer->update(['last_order_at' => now()]);

// After payment received
$b2bCustomer->decrement('outstanding_balance', $payment->amount);

// Generate customer number automatically (Observer)
// In CustomerObserver.php:
public function creating(Customer $customer)
{
    if (!$customer->customer_number) {
        $lastNumber = Customer::withTrashed()
            ->whereNotNull('customer_number')
            ->max('customer_number');

        $nextNumber = $lastNumber
            ? intval(substr($lastNumber, 5)) + 1
            : 1;

        $customer->customer_number = 'CUST-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
```

---

## 🔍 Query Examples

### Find products that need restock
```php
Product::where('is_closeout', false)
    ->where(function ($q) {
        $q->where('track_quantity', false)
            ->orWhereRaw('stock_quantity <= low_stock_threshold');
    })
    ->with('variants.stockLevels')
    ->get();
```

### Find active price rules for a customer
```php
use Cartino\Models\PriceRule;

$customer = auth()->user();
$customerGroupIds = $customer->customerGroups->pluck('id')->toArray();

PriceRule::active()
    ->withinTimeRange()
    ->withinUsageLimit()
    ->where(function ($q) use ($customerGroupIds) {
        $q->whereJsonContains('conditions->customer_group_ids', $customerGroupIds)
            ->orWhereNull('conditions->customer_group_ids');
    })
    ->byPriority()
    ->get();
```

### Orders stuck in processing
```php
Order::whereHas('state', function ($q) {
    $q->where('code', 'processing');
})
->where('created_at', '<', now()->subDays(3))
->with(['state', 'customer', 'lines'])
->get();
```

### Stock movements for audit
```php
use Cartino\Models\StockMovement;

// Last 7 days of movements
StockMovement::with(['stockLevel.productVariant', 'user'])
    ->where('created_at', '>=', now()->subDays(7))
    ->orderBy('created_at', 'desc')
    ->get();

// Adjustments only
StockMovement::where('type', 'adjustment')
    ->with(['user', 'stockLevel.warehouse'])
    ->get();
```

### Top customers by lifetime value
```php
Customer::where('state', 'active')
    ->orderBy('lifetime_value', 'desc')
    ->take(100)
    ->with('customerGroups')
    ->get();
```

---

## ⚡ Performance Tips

### 1. Eager Load Relations
```php
// BAD: N+1 queries
$products = Product::all();
foreach ($products as $product) {
    echo $product->bundles->count();  // N queries
}

// GOOD: Eager load
$products = Product::with('bundles')->get();
foreach ($products as $product) {
    echo $product->bundles->count();  // 0 extra queries
}
```

### 2. Cache Price Rules
```php
// Price rules change rarely, cache them
$activeRules = Cache::remember('price_rules:active', 3600, function () {
    return PriceRule::active()
        ->withinTimeRange()
        ->withinUsageLimit()
        ->byPriority()
        ->get();
});
```

### 3. Use Indexes
```php
// These queries are optimized with indexes:
Product::where('visibility', 'everywhere')->where('status', 'active')->get();
Order::where('state_id', 5)->where('is_test', false)->get();
StockLevel::where('warehouse_id', 1)->where('quantity_on_hand', '>', 0)->get();
```

### 4. Batch Operations
```php
// Update stock levels in batch
StockLevel::whereIn('product_variant_id', $variantIds)
    ->decrement('quantity_on_hand', 1);

// Bulk insert movements
StockMovement::insert($movements);
```

---

## 🐛 Troubleshooting

### Migration Errors

**Error**: `Class 'CreatePurchaseOrdersTable' not found`
**Fix**: Crea la migration mancante per `purchase_orders` table oppure commenta il riferimento in `stock_movements`

**Error**: `SQLSTATE[42S22]: Column not found: couriers`
**Fix**: Assicurati che la tabella `couriers` esista prima di eseguire le migrations fulfillments

### Reserved Stock Not Releasing

**Check**: Expired reservations cronjob running?
```bash
# Add to cron
* * * * * php artisan schedule:run >> /dev/null 2>&1

# Register in Kernel.php
$schedule->command('reservations:release')->everyFiveMinutes();
```

### Price Rules Not Applying

**Debug**:
```php
$calculator = app(\Cartino\Services\PriceCalculator::class);
$rules = $calculator->getApplicableRules($variant, $customer, $channel, $quantity, $cart);
dd($rules);  // Check which rules matched
```

---

## 📞 Support

Per domande o problemi:
1. Check documentation completa: `docs/PHASE_1_IMPLEMENTATION_COMPLETE.md`
2. Review platform comparison: `docs/PLATFORM_COMPARISON.md`
3. Check recommended implementations: `docs/RECOMMENDED_IMPLEMENTATIONS.md`

---

**Created**: 2025-12-15
**Version**: 1.0
**Author**: Claude Sonnet 4.5
